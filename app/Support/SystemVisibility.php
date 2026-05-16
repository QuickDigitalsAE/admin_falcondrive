<?php

namespace App\Support;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class SystemVisibility
{
    public const SUPER_ADMIN_ROLE_ID = 1;

    public static function superAdminRoleId(): int
    {
        return self::SUPER_ADMIN_ROLE_ID;
    }

    public static function hideSuperAdminRole(Builder $query, string $qualifiedColumn = 'id'): Builder
    {
        return $query->where($qualifiedColumn, '!=', self::superAdminRoleId());
    }

    public static function hideSuperAdminUsers(Builder $query): Builder
    {
        return $query->whereDoesntHave('roles', function ($roleQuery) {
            $roleQuery->where('roles.id', self::superAdminRoleId());
        });
    }

    public static function selectableRoles(): Builder
    {
        return self::hideSuperAdminRole(Role::query(), 'roles.id')
            ->where('guard_name', 'web')
            ->whereNull('deleted_at');
    }

    public static function isProtectedRole(?Role $role): bool
    {
        return (int) ($role?->id ?? 0) === self::superAdminRoleId();
    }

    public static function isProtectedRoleId(?int $roleId): bool
    {
        return (int) $roleId === self::superAdminRoleId();
    }

    public static function isSuperAdminUser(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->roles->contains(fn ($role) => (int) $role->id === self::superAdminRoleId());
    }
}
