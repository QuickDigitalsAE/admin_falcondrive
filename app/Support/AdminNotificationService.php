<?php

namespace App\Support;

use App\Models\AdminNotification;
use App\Models\Inquiry;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AdminNotificationService
{
    public static function notifyFromActivityLog(UserActivityLog $log): void
    {
        if (in_array($log->action, ['login', 'logout', 'failed_login'], true)) {
            return;
        }

        $subject = ActivityLogHelper::subjectLabel($log);
        $module = ActivityLogHelper::moduleLabel($log);

        self::createForRecipients(
            self::recipients()->filter(fn (User $user) => self::canReceiveActivityNotification($user, $log)),
            [
                'title' => self::titleForActivity($log, $module),
                'message' => ActivityLogHelper::summary($log),
                'icon' => self::iconForActivity($log->action),
                'color' => self::colorForActivity($log->action),
                'url' => self::urlForLog($log),
                'category' => 'activity',
                'data' => [
                    'action' => $log->action,
                    'module' => $module,
                    'table_name' => $log->table_name,
                    'subject' => $subject,
                    'log_id' => $log->id,
                    'performed_by' => $log->user?->name,
                    'performed_by_user_id' => $log->user_id,
                ],
            ]
        );
    }

    public static function notifyNewInquiry(Inquiry $inquiry): void
    {
        self::createForRecipients(
            self::recipients()->filter(fn (User $user) => self::canReceiveInquiryNotification($user)),
            [
                'title' => 'New inquiry received',
                'message' => collect([
                    $inquiry->name ? "From {$inquiry->name}" : null,
                    $inquiry->car_name ? "Car: {$inquiry->car_name}" : null,
                    $inquiry->promo_code ? "Promo: {$inquiry->promo_code}" : null,
                ])->filter()->implode(' | '),
                'icon' => 'fa-envelope-open-text',
                'color' => 'amber',
                'url' => route('admin.inquiries.show', $inquiry->id),
                'category' => 'inquiry',
                'data' => [
                    'inquiry_id' => $inquiry->id,
                    'table_name' => 'inquiries',
                    'name' => $inquiry->name,
                    'email' => $inquiry->email,
                    'number' => $inquiry->number,
                ],
            ]
        );
    }

    public static function formatForUi(AdminNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'title' => $notification->title,
            'message' => $notification->message,
            'actor_name' => data_get($notification->data, 'performed_by')
                ?: data_get($notification->data, 'name')
                ?: null,
            'icon' => $notification->icon,
            'color' => $notification->color,
            'url' => $notification->url ?: '#',
            'read_url' => route('admin.notifications.read', $notification->id),
            'category' => $notification->category,
            'is_read' => !is_null($notification->read_at),
            'time' => optional($notification->created_at)?->diffForHumans(),
            'created_at' => optional($notification->created_at)?->format('d M Y, h:i A'),
            'read_at' => optional($notification->read_at)?->format('d M Y, h:i A'),
        ];
    }

    public static function canUserViewNotification(User $user, AdminNotification $notification): bool
    {
        if (SystemVisibility::isSuperAdminUser($user)) {
            return true;
        }

        $tableName = data_get($notification->data, 'table_name');

        if ($notification->category === 'inquiry' && !$tableName) {
            $tableName = 'inquiries';
        }

        $permissions = self::permissionSetForTable($tableName);

        if (!$permissions) {
            return (int) $notification->user_id === (int) $user->id;
        }

        if ($user->can($permissions['view_all']) || $user->can($permissions['view'])) {
            return true;
        }

        if ($user->can($permissions['view_mine'])) {
            $actorId = (int) data_get($notification->data, 'performed_by_user_id', data_get($notification->data, 'user_id', 0));

            return $actorId > 0 ? $actorId === (int) $user->id : (int) $notification->user_id === (int) $user->id;
        }

        return false;
    }

    private static function recipients(): Collection
    {
        return User::query()
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->get();
    }

    private static function createForRecipients(Collection $users, array $payload): void
    {
        if ($users->isEmpty()) {
            return;
        }

        $now = now();
        $rows = $users->map(function (User $user) use ($payload, $now) {
            return [
                'user_id' => $user->id,
                'title' => $payload['title'],
                'message' => $payload['message'],
                'icon' => $payload['icon'] ?? 'fa-bell',
                'color' => $payload['color'] ?? 'amber',
                'url' => $payload['url'] ?? null,
                'category' => $payload['category'] ?? 'activity',
                'data' => json_encode($payload['data'] ?? [], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->all();

        AdminNotification::insert($rows);
    }

    private static function titleForActivity(UserActivityLog $log, string $module): string
    {
        return match ($log->action) {
            'created' => "{$module} created",
            'updated' => "{$module} updated",
            'deleted' => "{$module} deleted",
            'restored' => "{$module} restored",
            default => Str::headline($log->action ?: 'Activity'),
        };
    }

    private static function iconForActivity(string $action): string
    {
        return match ($action) {
            'created' => 'fa-circle-plus',
            'updated' => 'fa-pen-to-square',
            'deleted' => 'fa-trash-can',
            'restored' => 'fa-rotate-left',
            default => 'fa-bell',
        };
    }

    private static function colorForActivity(string $action): string
    {
        return match ($action) {
            'created' => 'emerald',
            'updated' => 'blue',
            'deleted' => 'red',
            'restored' => 'amber',
            default => 'amber',
        };
    }

    private static function urlForLog(UserActivityLog $log): string
    {
        $id = data_get($log->changes, 'id');

        return match ($log->table_name) {
            'users' => $id ? route('admin.users.show', $id) : route('admin.users'),
            'blogs' => $id ? route('admin.blogs.show', $id) : route('admin.blogs'),
            'inquiries' => $id ? route('admin.inquiries.show', $id) : route('admin.inquiries'),
            'cars' => $id ? route('admin.cars.show', $id) : route('admin.cars'),
            'car_with_drivers' => $id ? route('admin.car-with-drivers.show', $id) : route('admin.car-with-drivers'),
            'categories' => $id ? route('admin.categories.show', $id) : route('admin.categories'),
            'brands' => $id ? route('admin.brands.show', $id) : route('admin.brands'),
            'promotions' => $id ? route('admin.promotions.show', $id) : route('admin.promotions'),
            'lease' => $id ? route('admin.lease.show', $id) : route('admin.lease'),
            'locations' => $id ? route('admin.locations.show', $id) : route('admin.locations'),
            'settings' => $id ? route('admin.settings.show', $id) : route('admin.settings'),
            'testimonials' => $id ? route('admin.testimonials.show', $id) : route('admin.testimonials'),
            'highlights' => $id ? route('admin.highlights.show', $id) : route('admin.highlights'),
            'about_us' => $id ? route('admin.about-us.show', $id) : route('admin.about-us'),
            'faqs' => $id ? route('admin.faq.show', $id) : route('admin.faq'),
            'roles' => $id ? route('admin.roles.show', $id) : route('admin.roles'),
            'permissions' => $id ? route('admin.permissions.show', $id) : route('admin.permissions'),
            default => route('admin.dashboard'),
        };
    }

    private static function canReceiveActivityNotification(User $user, UserActivityLog $log): bool
    {
        if (SystemVisibility::isSuperAdminUser($user)) {
            return true;
        }

        $permissions = self::permissionSetForTable($log->table_name);

        if (!$permissions) {
            return (int) $log->user_id === (int) $user->id;
        }

        if ($user->can($permissions['view_all']) || $user->can($permissions['view'])) {
            return true;
        }

        if ($user->can($permissions['view_mine'])) {
            return (int) $log->user_id === (int) $user->id;
        }

        return false;
    }

    private static function canReceiveInquiryNotification(User $user): bool
    {
        if (SystemVisibility::isSuperAdminUser($user)) {
            return true;
        }

        $permissions = self::permissionSetForTable('inquiries');

        if (!$permissions) {
            return false;
        }

        return $user->can($permissions['view_all']) || $user->can($permissions['view']);
    }

    private static function permissionSetForTable(?string $tableName): ?array
    {
        $module = match ($tableName) {
            'users' => 'User',
            'blogs' => 'Blog',
            'highlights' => 'Highlight',
            'inquiries' => 'Inquiry',
            'cars' => 'Car',
            'car_with_drivers' => 'CarWithDriver',
            'about_us' => 'AboutUs',
            'brands' => 'Brand',
            'categories' => 'Category',
            'faqs' => 'Faq',
            'lease' => 'Lease',
            'locations' => 'Location',
            'promotions' => 'Promotion',
            'settings' => 'Setting',
            'testimonials' => 'Testimonial',
            'roles' => 'Role',
            'permissions' => 'Permissions',
            default => null,
        };

        if (!$module) {
            return null;
        }

        return [
            'view_all' => "{$module}_ViewAll",
            'view_mine' => "{$module}_ViewMine",
            'view' => "{$module}_View",
        ];
    }
}
