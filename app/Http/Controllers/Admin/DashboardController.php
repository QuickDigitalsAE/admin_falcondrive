<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutUs;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Lease;
use App\Models\Location;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Testimonial;
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
                'visible' => $user->can('AboutUs_Menu'),
                'title' => 'About Us',
                'description' => 'Bilingual company story and SEO content',
                'count' => AboutUs::query()->count(),
                'icon' => 'fa-circle-info',
                'url' => route('admin.about-us'),
            ],
            [
                'visible' => $user->can('Brand_Menu'),
                'title' => 'Brands',
                'description' => 'Car brands and brand SEO pages',
                'count' => Brand::query()->count(),
                'icon' => 'fa-copyright',
                'url' => route('admin.brands'),
            ],
            [
                'visible' => $user->can('Category_Menu'),
                'title' => 'Categories',
                'description' => 'Vehicle category content and structure',
                'count' => Category::query()->count(),
                'icon' => 'fa-layer-group',
                'url' => route('admin.categories'),
            ],
            [
                'visible' => $user->can('Faq_Menu'),
                'title' => 'FAQ',
                'description' => 'Frequently asked questions',
                'count' => Faq::query()->count(),
                'icon' => 'fa-circle-question',
                'url' => route('admin.faq'),
            ],
            [
                'visible' => $user->can('Lease_Menu'),
                'title' => 'Lease',
                'description' => 'Lease landing and SEO content',
                'count' => Lease::query()->count(),
                'icon' => 'fa-file-signature',
                'url' => route('admin.lease'),
            ],
            [
                'visible' => $user->can('Location_Menu'),
                'title' => 'Locations',
                'description' => 'Location landing pages and SEO',
                'count' => Location::query()->count(),
                'icon' => 'fa-location-dot',
                'url' => route('admin.locations'),
            ],
            [
                'visible' => $user->can('Testimonial_Menu'),
                'title' => 'Testimonials',
                'description' => 'Customer review content',
                'count' => Testimonial::query()->count(),
                'icon' => 'fa-comments',
                'url' => route('admin.testimonials'),
            ],
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
