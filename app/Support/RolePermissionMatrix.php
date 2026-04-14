<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RolePermissionMatrix
{
    public static function levels(): array
    {
        return config('role_permissions.levels', []);
    }

    public static function levelOptions(): array
    {
        return array_keys(self::levels());
    }

    public static function label(string $level): string
    {
        return self::levels()[$level]['label'] ?? Str::headline($level);
    }

    public static function description(string $level): string
    {
        return self::levels()[$level]['description'] ?? '';
    }

    public static function patterns(string $level): array
    {
        return self::levels()[$level]['patterns'] ?? [];
    }

    public static function allows(string $level, string $permissionName): bool
    {
        $patterns = self::patterns($level);

        foreach ($patterns as $pattern) {
            if ($pattern === '*' || Str::is($pattern, $permissionName)) {
                return true;
            }
        }

        return false;
    }

    public static function allowedPermissionNames(string $level, iterable $permissions): array
    {
        return collect($permissions)
            ->map(function ($permission) {
                return is_string($permission) ? $permission : $permission->name;
            })
            ->filter(fn ($permissionName) => self::allows($level, $permissionName))
            ->values()
            ->all();
    }

    public static function allowedLevels(string $permissionName): array
    {
        return collect(self::levelOptions())
            ->filter(fn ($level) => self::allows($level, $permissionName))
            ->values()
            ->all();
    }

    public static function groupedPermissions(Collection $permissions): Collection
    {
        return $permissions
            ->sortBy('name')
            ->groupBy(fn ($permission) => Str::before($permission->name, '_'))
            ->map(function (Collection $group) {
                return $group->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'table_name' => $permission->table_name,
                        'allowed_levels' => self::allowedLevels($permission->name),
                    ];
                })->values();
            });
    }
}
