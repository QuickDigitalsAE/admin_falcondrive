<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use App\Support\SystemVisibility;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Dashboard_View');
    }

    public function index()
    {
        $usersQuery = SystemVisibility::hideSuperAdminUsers(User::query());

        $latestUsers = (clone $usersQuery)
            ->with('roles')
            ->latest()
            ->take(6)
            ->get();

        $recentActivity = $latestUsers->map(function (User $user, int $index) {
            return [
                'title' => $user->name . ' profile synced',
                'meta' => optional($user->roles->first())->name ?: 'No role assigned',
                'time' => match ($index) {
                    0 => '2 min ago',
                    1 => '10 min ago',
                    2 => '1 hour ago',
                    default => optional($user->created_at)->format('d M Y'),
                },
            ];
        });

        $modules = $this->dashboardModules();

        return view('admin.dashboard.index', [
            'totalUsers' => (clone $usersQuery)->count(),
            'activeUsers' => (clone $usersQuery)->where('status', true)->count(),
            'inactiveUsers' => (clone $usersQuery)->where('status', false)->count(),
            'totalRoles' => SystemVisibility::hideSuperAdminRole(Role::query(), 'roles.id')->count(),
            'totalPermissions' => Permission::query()->count(),
            'latestUsers' => $latestUsers,
            'modules' => $modules,
            'recentActivity' => $recentActivity,
        ]);
    }

    private function dashboardModules(): Collection
    {
        $user = auth()->user();

        return collect([
            [
                'visible' => $user->can('User_Menu'),
                'title' => 'Users',
                'description' => 'Manage admin panel users',
                'count' => SystemVisibility::hideSuperAdminUsers(User::query())->count(),
                'icon' => 'fa-users',
                'url' => route('admin.users'),
            ],
            [
                'visible' => $user->can('Role_Menu'),
                'title' => 'Roles',
                'description' => 'Access levels and hierarchy',
                'count' => SystemVisibility::hideSuperAdminRole(Role::query(), 'roles.id')->count(),
                'icon' => 'fa-user-tag',
                'url' => route('admin.roles'),
            ],
            [
                'visible' => $user->can('Permissions_Menu'),
                'title' => 'Permissions',
                'description' => 'Permission matrix and actions',
                'count' => Permission::query()->count(),
                'icon' => 'fa-key',
                'url' => route('admin.permissions'),
            ],
            [
                'visible' => true,
                'title' => 'Profile',
                'description' => 'Update your account details',
                'count' => null,
                'icon' => 'fa-id-badge',
                'url' => route('admin.account.profile'),
            ],
            [
                'visible' => true,
                'title' => 'Settings',
                'description' => 'Security and password settings',
                'count' => null,
                'icon' => 'fa-gear',
                'url' => route('admin.account.settings'),
            ],
        ])->filter(fn ($module) => $module['visible'])->values();
    }
}
