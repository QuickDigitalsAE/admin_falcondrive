<?php

namespace App\Support;

use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ActivityLogHelper
{
    public static function resolveIpAddress(?Request $request = null): ?string
    {
        $requestIp = trim((string) ($request?->ip() ?? ''));

        if ($requestIp !== '' && !self::isLoopbackIp($requestIp)) {
            return $requestIp;
        }

        $serverIp = trim((string) gethostbyname(gethostname()));
        if ($serverIp !== '' && !self::isLoopbackIp($serverIp)) {
            return $serverIp;
        }

        $hostIps = gethostbynamel(gethostname()) ?: [];
        foreach ($hostIps as $ip) {
            $ip = trim((string) $ip);
            if ($ip !== '' && !self::isLoopbackIp($ip)) {
                return $ip;
            }
        }

        return $requestIp !== '' ? $requestIp : null;
    }

    public static function logAuth(string $action, ?User $user = null, array $changes = []): void
    {
        UserActivityLog::create([
            'user_id' => $user?->id,
            'model_type' => User::class,
            'table_name' => 'users',
            'action' => $action,
            'changes' => self::sanitize($changes),
        ]);
    }

    public static function category(UserActivityLog $log): string
    {
        return in_array($log->action, ['login', 'logout', 'failed_login'], true) ? 'auth' : 'activity';
    }

    public static function actionLabel(string $action): string
    {
        return match ($action) {
            'login' => 'Login',
            'logout' => 'Logout',
            'failed_login' => 'Failed Login',
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'restored' => 'Restored',
            default => Str::headline(str_replace('_', ' ', $action)),
        };
    }

    public static function moduleLabel(UserActivityLog $log): string
    {
        if (!empty($log->table_name)) {
            return Str::headline(Str::singular(str_replace('_', ' ', $log->table_name)));
        }

        if (!empty($log->model_type)) {
            return Str::headline(class_basename($log->model_type));
        }

        return 'System';
    }

    public static function summary(UserActivityLog $log): string
    {
        $module = self::moduleLabel($log);
        $subject = self::subjectLabel($log);

        return match ($log->action) {
            'login' => $subject !== 'Unknown user'
                ? "{$subject} signed in successfully."
                : 'A login was completed.',
            'logout' => $subject !== 'Unknown user'
                ? "{$subject} signed out of the system."
                : 'A logout was completed.',
            'failed_login' => $subject !== 'Unknown user'
                ? "Failed login attempt for {$subject}."
                : 'A login attempt failed.',
            'created' => "{$module} created" . ($subject !== 'Unknown record' ? ": {$subject}." : '.'),
            'updated' => "{$module} updated" . ($subject !== 'Unknown record' ? ": {$subject}." : '.'),
            'deleted' => "{$module} deleted" . ($subject !== 'Unknown record' ? ": {$subject}." : '.'),
            'restored' => "{$module} restored" . ($subject !== 'Unknown record' ? ": {$subject}." : '.'),
            default => self::actionLabel($log->action) . " recorded for {$module}.",
        };
    }

    public static function subjectLabel(UserActivityLog $log): string
    {
        $changes = $log->changes ?? [];

        if (in_array($log->action, ['login', 'logout', 'failed_login'], true)) {
            return self::extractSubjectValue(Arr::get($changes, 'user_name'))
                ?: self::extractSubjectValue(Arr::get($changes, 'email'))
                ?: 'Unknown user';
        }

        return self::extractSubjectValue(Arr::get($changes, 'name'))
            ?: self::extractSubjectValue(Arr::get($changes, 'title'))
            ?: self::extractSubjectValue(Arr::get($changes, 'email'))
            ?: self::extractSubjectValue(Arr::get($changes, 'id'))
            ?: 'Unknown record';
    }

    public static function detailLines(UserActivityLog $log): array
    {
        $changes = $log->changes ?? [];
        $details = [];

        if (in_array($log->action, ['login', 'logout', 'failed_login'], true)) {
            $details[] = 'Email: ' . (Arr::get($changes, 'email', 'N/A'));

            if ($ip = Arr::get($changes, 'ip_address')) {
                $details[] = 'IP Address: ' . $ip;
            }

            if ($browser = Arr::get($changes, 'user_agent')) {
                $details[] = 'Device: ' . Str::limit($browser, 110);
            }

            return $details;
        }

        foreach ($changes as $field => $value) {
            if (in_array($field, ['id', 'name', 'email', 'title'], true)) {
                continue;
            }

            if (is_array($value) && array_key_exists('old', $value) && array_key_exists('new', $value)) {
                $details[] = sprintf(
                    '%s: %s -> %s',
                    Str::headline(str_replace('_', ' ', $field)),
                    self::stringify($value['old']),
                    self::stringify($value['new'])
                );

                continue;
            }

            $details[] = sprintf(
                '%s: %s',
                Str::headline(str_replace('_', ' ', $field)),
                self::stringify($value)
            );
        }

        return array_values(array_filter($details));
    }

    public static function exportDetail(UserActivityLog $log): string
    {
        return implode(' | ', self::detailLines($log));
    }

    protected static function sanitize(array $changes): array
    {
        foreach (['password', 'remember_token'] as $field) {
            if (array_key_exists($field, $changes)) {
                $changes[$field] = '[hidden]';
            }
        }

        return $changes;
    }

    protected static function stringify($value): string
    {
        if ($value === null || $value === '') {
            return 'N/A';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return Str::limit((string) $value, 120);
    }

    protected static function extractSubjectValue($value): ?string
    {
        if (is_array($value)) {
            $value = $value['new'] ?? $value['old'] ?? null;
        }

        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_scalar($value)) {
            return Str::limit((string) $value, 120);
        }

        return null;
    }

    protected static function isLoopbackIp(string $ip): bool
    {
        return in_array($ip, ['::1', '127.0.0.1', 'localhost'], true)
            || str_starts_with($ip, '127.');
    }
}
