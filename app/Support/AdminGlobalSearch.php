<?php

namespace App\Support;

use App\Models\AboutUs;
use App\Models\Blog;
use App\Models\Brand;
use App\Models\Car;
use App\Models\CarWithDriver;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Faq;
use App\Models\Highlight;
use App\Models\Inquiry;
use App\Models\Booking;
use App\Models\Lease;
use App\Models\Location;
use App\Models\Permission;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AdminGlobalSearch
{
    public static function search(?Authenticatable $user, string $term, int $limitPerModule = 6): array
    {
        $user = $user instanceof User ? $user : null;
        $term = trim($term);

        if (!$user || $term === '') {
            return [
                'query' => $term,
                'total_results' => 0,
                'groups' => [],
                'quick_links' => self::quickLinks($user, $term),
            ];
        }

        $groups = collect(self::modules())
            ->filter(fn (array $module) => self::canAccessModule($user, $module))
            ->map(function (array $module) use ($user, $term, $limitPerModule) {
                $query = ($module['model'])::query();

                self::applyBaseConstraints($query, $module);
                self::applySearchConstraints($query, $module, $term);

                if (isset($module['query']) && is_callable($module['query'])) {
                    $module['query']($query, $user, $term);
                }

                if (!empty($module['order_by'])) {
                    foreach ($module['order_by'] as [$column, $direction]) {
                        $query->orderBy($column, $direction);
                    }
                }

                $items = $query->limit($limitPerModule)->get();

                if ($items->isEmpty()) {
                    return null;
                }

                return [
                    'key' => $module['key'],
                    'label' => $module['label'],
                    'icon' => $module['icon'],
                    'index_url' => route($module['routes']['index']),
                    'items' => $items->map(fn (Model $record) => self::mapRecord($record, $module, $user))->all(),
                ];
            })
            ->filter()
            ->values()
            ->all();

        return [
            'query' => $term,
            'total_results' => collect($groups)->sum(fn (array $group) => count($group['items'])),
            'groups' => $groups,
            'quick_links' => self::quickLinks($user, $term),
        ];
    }

    public static function quickLinks(?Authenticatable $user, string $term): array
    {
        $user = $user instanceof User ? $user : null;
        $term = Str::lower(trim($term));

        return collect(self::modules())
            ->filter(fn (array $module) => $user && self::canAccessModule($user, $module))
            ->filter(function (array $module) use ($term) {
                if ($term === '') {
                    return true;
                }

                return str_contains(Str::lower($module['label']), $term);
            })
            ->map(fn (array $module) => [
                'label' => $module['label'],
                'icon' => $module['icon'],
                'url' => route($module['routes']['index']),
            ])
            ->values()
            ->all();
    }

    private static function mapRecord(Model $record, array $module, User $user): array
    {
        $showUrl = self::buildRoute($module['routes']['show'] ?? null, $record, $module);
        $editUrl = self::buildRoute($module['routes']['edit'] ?? null, $record, $module);

        $actions = collect([
            self::canPerform($user, $module['view_permissions'] ?? [])
                ? ['label' => 'View', 'icon' => 'fa-eye', 'url' => $showUrl]
                : null,
            self::canPerform($user, $module['edit_permissions'] ?? [])
                ? ['label' => 'Edit', 'icon' => 'fa-pen-to-square', 'url' => $editUrl]
                : null,
        ])->filter(fn ($action) => !empty($action['url']))->values()->all();

        return [
            'id' => $record->getKey(),
            'title' => value($module['title'], $record),
            'subtitle' => value($module['subtitle'], $record),
            'meta' => value($module['meta'], $record),
            'badge' => $module['label'],
            'show_url' => $showUrl,
            'edit_url' => $editUrl,
            'actions' => $actions,
        ];
    }

    private static function buildRoute(?string $routeName, Model $record, array $module): ?string
    {
        if (!$routeName) {
            return null;
        }

        $parameter = isset($module['route_key']) && $module['route_key'] !== 'id'
            ? $record->{$module['route_key']}
            : $record->getKey();

        return route($routeName, $parameter);
    }

    private static function canAccessModule(User $user, array $module): bool
    {
        return self::canPerform($user, $module['menu_permissions'] ?? []);
    }

    private static function canPerform(User $user, array $permissions): bool
    {
        if (empty($permissions)) {
            return false;
        }

        return collect($permissions)->contains(fn (string $permission) => $user->can($permission));
    }

    private static function applyBaseConstraints(Builder $query, array $module): void
    {
        $modelClass = $module['model'];

        if (in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            $query->whereNull($query->getModel()->getQualifiedDeletedAtColumn());
        }

        if (!empty($module['base_where'])) {
            foreach ($module['base_where'] as $column => $value) {
                $query->where($column, $value);
            }
        }
    }

    private static function applySearchConstraints(Builder $query, array $module, string $term): void
    {
        $searchable = $module['searchable'] ?? [];

        $query->where(function (Builder $builder) use ($searchable, $term, $module) {
            foreach ($searchable as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $builder->{$method}($column, 'LIKE', '%' . $term . '%');
            }

            if (!empty($module['search_relations'])) {
                foreach ($module['search_relations'] as $relation => $columns) {
                    $builder->orWhereHas($relation, function (Builder $relationQuery) use ($columns, $term) {
                        $relationQuery->where(function (Builder $nestedQuery) use ($columns, $term) {
                            foreach ($columns as $index => $column) {
                                $method = $index === 0 ? 'where' : 'orWhere';
                                $nestedQuery->{$method}($column, 'LIKE', '%' . $term . '%');
                            }
                        });
                    });
                }
            }
        });
    }

    private static function modules(): array
    {
        return [
            [
                'key' => 'users',
                'label' => 'Users',
                'icon' => 'fa-users',
                'model' => User::class,
                'menu_permissions' => ['User_Menu'],
                'view_permissions' => ['User_ViewAll', 'User_View'],
                'edit_permissions' => ['User_Edit'],
                'routes' => ['index' => 'admin.users', 'show' => 'admin.users.show', 'edit' => 'admin.users.edit'],
                'searchable' => ['name', 'email', 'phone', 'emp_id'],
                'title' => fn (User $record) => $record->name ?: 'User #' . $record->id,
                'subtitle' => fn (User $record) => collect([$record->email, $record->phone])->filter()->implode(' | '),
                'meta' => fn (User $record) => collect([$record->emp_id ? 'Employee ID: ' . $record->emp_id : null, $record->status ? 'Status: ' . $record->status : null])->filter()->implode(' | '),
                'query' => fn (Builder $query, ?User $user = null, string $term = '') => SystemVisibility::hideSuperAdminUsers($query),
                'order_by' => [['name', 'asc']],
            ],
            [
                'key' => 'customers',
                'label' => 'Customers',
                'icon' => 'fa-address-card',
                'model' => Customer::class,
                'menu_permissions' => ['Customer_Menu'],
                'view_permissions' => ['Customer_ViewAll', 'Customer_View'],
                'edit_permissions' => ['Customer_Edit'],
                'routes' => ['index' => 'admin.customers', 'show' => 'admin.customers.show', 'edit' => 'admin.customers.edit'],
                'searchable' => ['customer_id', 'username', 'first_name', 'last_name', 'email', 'mobile_no', 'nationality', 'city', 'country'],
                'title' => fn (Customer $record) => trim(($record->first_name ?? '') . ' ' . ($record->last_name ?? '')) ?: ('Customer #' . $record->id),
                'subtitle' => fn (Customer $record) => collect([$record->email, $record->mobile_no])->filter()->implode(' | '),
                'meta' => fn (Customer $record) => collect([
                    $record->customer_id ? 'Speed ID: ' . $record->customer_id : null,
                    $record->username ? 'Username: ' . $record->username : null,
                ])->filter()->implode(' | '),
                'order_by' => [['created_at', 'desc']],
            ],
            [
                'key' => 'blogs',
                'label' => 'Blogs',
                'icon' => 'fa-blog',
                'model' => Blog::class,
                'menu_permissions' => ['Blog_Menu'],
                'view_permissions' => ['Blog_ViewAll', 'Blog_View'],
                'edit_permissions' => ['Blog_Edit'],
                'routes' => ['index' => 'admin.blogs', 'show' => 'admin.blogs.show', 'edit' => 'admin.blogs.edit'],
                'searchable' => ['title_en', 'title_ar', 'slug', 'seo_title_en', 'seo_title_ar'],
                'title' => fn (Blog $record) => $record->title_en ?: ($record->title_ar ?: 'Blog #' . $record->id),
                'subtitle' => fn (Blog $record) => $record->slug ?: 'Blog entry',
                'meta' => fn (Blog $record) => optional($record->blog_schedule)->format('d M Y, h:i A') ?: 'Scheduled blog',
                'order_by' => [['created_at', 'desc']],
            ],
            [
                'key' => 'highlights',
                'label' => 'Highlights',
                'icon' => 'fa-star',
                'model' => Highlight::class,
                'menu_permissions' => ['Highlight_Menu'],
                'view_permissions' => ['Highlight_ViewAll', 'Highlight_View'],
                'edit_permissions' => ['Highlight_Edit'],
                'routes' => ['index' => 'admin.highlights', 'show' => 'admin.highlights.show', 'edit' => 'admin.highlights.edit'],
                'searchable' => ['title_en', 'title_ar'],
                'title' => fn (Highlight $record) => $record->title_en ?: ($record->title_ar ?: 'Highlight #' . $record->id),
                'subtitle' => fn (?Highlight $record = null) => 'Homepage highlight',
                'meta' => fn (Highlight $record) => 'Record #' . $record->id,
                'order_by' => [['created_at', 'desc']],
            ],
            [
                'key' => 'inquiries',
                'label' => 'Inquiries',
                'icon' => 'fa-envelope-open-text',
                'model' => Inquiry::class,
                'menu_permissions' => ['Inquiry_Menu'],
                'view_permissions' => ['Inquiry_ViewAll', 'Inquiry_View'],
                'edit_permissions' => ['Inquiry_Edit'],
                'routes' => ['index' => 'admin.inquiries', 'show' => 'admin.inquiries.show', 'edit' => 'admin.inquiries.edit'],
                'searchable' => ['name', 'email', 'number', 'message', 'promo_code', 'car_name'],
                'title' => fn (Inquiry $record) => $record->name ?: 'Inquiry #' . $record->id,
                'subtitle' => fn (Inquiry $record) => collect([$record->email, $record->number])->filter()->implode(' | '),
                'meta' => fn (Inquiry $record) => Str::limit($record->message ?: ($record->car_name ?: 'Inquiry received'), 90),
                'order_by' => [['created_at', 'desc']],
            ],
            [
                'key' => 'bookings',
                'label' => 'Bookings',
                'icon' => 'fa-calendar-check',
                'model' => Booking::class,
                'menu_permissions' => ['Booking_Menu'],
                'view_permissions' => ['Booking_ViewAll', 'Booking_View'],
                'edit_permissions' => ['Booking_Edit'],
                'routes' => ['index' => 'admin.bookings', 'show' => 'admin.bookings.show', 'edit' => 'admin.bookings.edit'],
                'searchable' => ['name', 'email', 'number', 'coupon_code', 'paid_id', 'paid_status', 'paid_via'],
                'title' => fn (Booking $record) => $record->name ?: 'Booking #' . $record->id,
                'subtitle' => fn (Booking $record) => collect([$record->email, $record->number])->filter()->implode(' | '),
                'meta' => fn (Booking $record) => Str::limit($record->notes ?: ($record->description ?: 'Booking received'), 90),
                'order_by' => [['created_at', 'desc']],
            ],
            [
                'key' => 'cars',
                'label' => 'Cars',
                'icon' => 'fa-car-side',
                'model' => Car::class,
                'menu_permissions' => ['Car_Menu'],
                'view_permissions' => ['Car_ViewAll', 'Car_View'],
                'edit_permissions' => ['Car_Edit'],
                'routes' => ['index' => 'admin.cars', 'show' => 'admin.cars.show', 'edit' => 'admin.cars.edit'],
                'searchable' => ['name_en', 'name_ar', 'slug', 'model'],
                'search_relations' => ['brand' => ['name_en', 'name_ar']],
                'title' => fn (Car $record) => $record->name_en ?: ($record->name_ar ?: 'Car #' . $record->id),
                'subtitle' => fn (Car $record) => collect([$record->brand?->name_en, $record->model])->filter()->implode(' | '),
                'meta' => fn (Car $record) => collect([
                    $record->price_daily ? 'Daily: ' . $record->price_daily : null,
                    $record->stock ? 'In stock' : 'Out of stock',
                ])->filter()->implode(' | '),
                'query' => fn (Builder $query, ?User $user = null, string $term = '') => $query->with('brand'),
                'order_by' => [['name_en', 'asc']],
            ],
            [
                'key' => 'car_with_drivers',
                'label' => 'Car With Driver',
                'icon' => 'fa-id-card-clip',
                'model' => CarWithDriver::class,
                'menu_permissions' => ['CarWithDriver_Menu'],
                'view_permissions' => ['CarWithDriver_ViewAll', 'CarWithDriver_View'],
                'edit_permissions' => ['CarWithDriver_Edit'],
                'routes' => ['index' => 'admin.car-with-drivers', 'show' => 'admin.car-with-drivers.show', 'edit' => 'admin.car-with-drivers.edit'],
                'searchable' => ['display_en', 'display_ar', 'slug', 'header_en', 'header_ar'],
                'title' => fn (CarWithDriver $record) => $record->display_en ?: ($record->display_ar ?: 'Driver Page #' . $record->id),
                'subtitle' => fn (CarWithDriver $record) => $record->slug ?: 'Driver service page',
                'meta' => fn (CarWithDriver $record) => Str::limit(strip_tags($record->header_en ?: $record->header_ar ?: ''), 90),
                'order_by' => [['display_en', 'asc']],
            ],
            [
                'key' => 'about_us',
                'label' => 'About Us',
                'icon' => 'fa-circle-info',
                'model' => AboutUs::class,
                'menu_permissions' => ['AboutUs_Menu'],
                'view_permissions' => ['AboutUs_ViewAll', 'AboutUs_View'],
                'edit_permissions' => ['AboutUs_Edit'],
                'routes' => ['index' => 'admin.about-us', 'show' => 'admin.about-us.show', 'edit' => 'admin.about-us.edit'],
                'searchable' => ['first_section_en', 'first_section_ar', 'mission_en', 'mission_ar', 'vision_en', 'vision_ar', 'seo_title_en', 'seo_title_ar'],
                'title' => fn (AboutUs $record) => 'About Us Content',
                'subtitle' => fn (AboutUs $record) => Str::limit(strip_tags($record->first_section_en ?: $record->first_section_ar ?: ''), 70),
                'meta' => fn (?AboutUs $record = null) => 'Content section',
                'order_by' => [['id', 'desc']],
            ],
            [
                'key' => 'brands',
                'label' => 'Brands',
                'icon' => 'fa-copyright',
                'model' => Brand::class,
                'menu_permissions' => ['Brand_Menu'],
                'view_permissions' => ['Brand_ViewAll', 'Brand_View'],
                'edit_permissions' => ['Brand_Edit'],
                'routes' => ['index' => 'admin.brands', 'show' => 'admin.brands.show', 'edit' => 'admin.brands.edit'],
                'searchable' => ['name_en', 'name_ar', 'slug', 'seo_title_en', 'seo_title_ar'],
                'title' => fn (Brand $record) => $record->name_en ?: ($record->name_ar ?: 'Brand #' . $record->id),
                'subtitle' => fn (Brand $record) => $record->slug ?: 'Brand',
                'meta' => fn (Brand $record) => Str::limit(strip_tags($record->description_en ?: $record->description_ar ?: ''), 90),
                'order_by' => [['name_en', 'asc']],
            ],
            [
                'key' => 'categories',
                'label' => 'Categories',
                'icon' => 'fa-layer-group',
                'model' => Category::class,
                'menu_permissions' => ['Category_Menu'],
                'view_permissions' => ['Category_ViewAll', 'Category_View'],
                'edit_permissions' => ['Category_Edit'],
                'routes' => ['index' => 'admin.categories', 'show' => 'admin.categories.show', 'edit' => 'admin.categories.edit'],
                'searchable' => ['name_en', 'name_ar', 'slug', 'seo_title_en', 'seo_title_ar'],
                'title' => fn (Category $record) => $record->name_en ?: ($record->name_ar ?: 'Category #' . $record->id),
                'subtitle' => fn (Category $record) => $record->slug ?: 'Category',
                'meta' => fn (Category $record) => Str::limit(strip_tags($record->description_en ?: $record->description_ar ?: ''), 90),
                'order_by' => [['name_en', 'asc']],
            ],
            [
                'key' => 'faq',
                'label' => 'FAQ',
                'icon' => 'fa-circle-question',
                'model' => Faq::class,
                'menu_permissions' => ['Faq_Menu'],
                'view_permissions' => ['Faq_ViewAll', 'Faq_View'],
                'edit_permissions' => ['Faq_Edit'],
                'routes' => ['index' => 'admin.faq', 'show' => 'admin.faq.show', 'edit' => 'admin.faq.edit'],
                'searchable' => ['question_en', 'question_ar', 'answer_en', 'answer_ar'],
                'title' => fn (Faq $record) => Str::limit(strip_tags($record->question_en ?: $record->question_ar ?: 'FAQ #' . $record->id), 90),
                'subtitle' => fn (?Faq $record = null) => 'Frequently asked question',
                'meta' => fn (Faq $record) => Str::limit(strip_tags($record->answer_en ?: $record->answer_ar ?: ''), 90),
                'order_by' => [['created_at', 'desc']],
            ],
            [
                'key' => 'lease',
                'label' => 'Lease',
                'icon' => 'fa-file-signature',
                'model' => Lease::class,
                'menu_permissions' => ['Lease_Menu'],
                'view_permissions' => ['Lease_ViewAll', 'Lease_View'],
                'edit_permissions' => ['Lease_Edit'],
                'routes' => ['index' => 'admin.lease', 'show' => 'admin.lease.show', 'edit' => 'admin.lease.edit'],
                'searchable' => ['name_en', 'name_ar', 'slug', 'seo_title_en', 'seo_title_ar'],
                'title' => fn (Lease $record) => $record->name_en ?: ($record->name_ar ?: 'Lease #' . $record->id),
                'subtitle' => fn (Lease $record) => $record->slug ?: 'Lease page',
                'meta' => fn (Lease $record) => Str::limit(strip_tags($record->description_en ?: $record->description_ar ?: ''), 90),
                'order_by' => [['name_en', 'asc']],
            ],
            [
                'key' => 'locations',
                'label' => 'Locations',
                'icon' => 'fa-location-dot',
                'model' => Location::class,
                'menu_permissions' => ['Location_Menu'],
                'view_permissions' => ['Location_ViewAll', 'Location_View'],
                'edit_permissions' => ['Location_Edit'],
                'routes' => ['index' => 'admin.locations', 'show' => 'admin.locations.show', 'edit' => 'admin.locations.edit'],
                'searchable' => ['name_en', 'name_ar', 'slug', 'seo_title_en', 'seo_title_ar'],
                'title' => fn (Location $record) => $record->name_en ?: ($record->name_ar ?: 'Location #' . $record->id),
                'subtitle' => fn (Location $record) => $record->slug ?: 'Location page',
                'meta' => fn (Location $record) => Str::limit(strip_tags($record->description_en ?: $record->description_ar ?: ''), 90),
                'order_by' => [['name_en', 'asc']],
            ],
            [
                'key' => 'promotions',
                'label' => 'Promotions',
                'icon' => 'fa-tags',
                'model' => Promotion::class,
                'menu_permissions' => ['Promotion_Menu'],
                'view_permissions' => ['Promotion_ViewAll', 'Promotion_View'],
                'edit_permissions' => ['Promotion_Edit'],
                'routes' => ['index' => 'admin.promotions', 'show' => 'admin.promotions.show', 'edit' => 'admin.promotions.edit'],
                'searchable' => ['name_en', 'name_ar', 'slug', 'seo_title_en', 'seo_title_ar'],
                'title' => fn (Promotion $record) => $record->name_en ?: ($record->name_ar ?: 'Promotion #' . $record->id),
                'subtitle' => fn (Promotion $record) => $record->slug ?: 'Promotion',
                'meta' => fn (Promotion $record) => Str::limit(strip_tags($record->description_en ?: $record->description_ar ?: ''), 90),
                'order_by' => [['created_at', 'desc']],
            ],
            [
                'key' => 'settings',
                'label' => 'Settings',
                'icon' => 'fa-sliders',
                'model' => Setting::class,
                'menu_permissions' => ['Setting_Menu'],
                'view_permissions' => ['Setting_ViewAll', 'Setting_View'],
                'edit_permissions' => ['Setting_Edit'],
                'routes' => ['index' => 'admin.settings', 'show' => 'admin.settings.show', 'edit' => 'admin.settings.edit'],
                'searchable' => ['key', 'display_name', 'value', 'group', 'type'],
                'title' => fn (Setting $record) => $record->display_name ?: $record->key,
                'subtitle' => fn (Setting $record) => $record->key,
                'meta' => fn (Setting $record) => collect([$record->group, $record->type])->filter()->implode(' | '),
                'order_by' => [['order', 'asc']],
            ],
            [
                'key' => 'testimonials',
                'label' => 'Testimonials',
                'icon' => 'fa-comments',
                'model' => Testimonial::class,
                'menu_permissions' => ['Testimonial_Menu'],
                'view_permissions' => ['Testimonial_ViewAll', 'Testimonial_View'],
                'edit_permissions' => ['Testimonial_Edit'],
                'routes' => ['index' => 'admin.testimonials', 'show' => 'admin.testimonials.show', 'edit' => 'admin.testimonials.edit'],
                'searchable' => ['name_en', 'name_ar', 'description_en', 'description_ar'],
                'title' => fn (Testimonial $record) => $record->name_en ?: ($record->name_ar ?: 'Testimonial #' . $record->id),
                'subtitle' => fn (?Testimonial $record = null) => 'Client testimonial',
                'meta' => fn (Testimonial $record) => Str::limit(strip_tags($record->description_en ?: $record->description_ar ?: ''), 90),
                'order_by' => [['created_at', 'desc']],
            ],
            [
                'key' => 'roles',
                'label' => 'Roles',
                'icon' => 'fa-user-tag',
                'model' => Role::class,
                'menu_permissions' => ['Role_Menu'],
                'view_permissions' => ['Role_ViewAll', 'Role_View'],
                'edit_permissions' => ['Role_Edit'],
                'routes' => ['index' => 'admin.roles', 'show' => 'admin.roles.show', 'edit' => 'admin.roles.edit'],
                'searchable' => ['name', 'role_level'],
                'base_where' => ['guard_name' => 'web'],
                'title' => fn (Role $record) => $record->name ?: 'Role #' . $record->id,
                'subtitle' => fn (Role $record) => RolePermissionMatrix::label($record->role_level),
                'meta' => fn (Role $record) => 'Permissions: ' . $record->permissions()->count(),
                'query' => fn (Builder $query, ?User $user = null, string $term = '') => SystemVisibility::hideSuperAdminRole($query, 'roles.id'),
                'order_by' => [['name', 'asc']],
            ],
            [
                'key' => 'permissions',
                'label' => 'Permissions',
                'icon' => 'fa-key',
                'model' => Permission::class,
                'menu_permissions' => ['Permissions_Menu'],
                'view_permissions' => ['Permissions_ViewAll', 'Permissions_View'],
                'edit_permissions' => ['Permissions_Edit'],
                'routes' => ['index' => 'admin.permissions', 'show' => 'admin.permissions.show', 'edit' => 'admin.permissions.edit'],
                'searchable' => ['name', 'table_name'],
                'base_where' => ['guard_name' => 'web'],
                'title' => fn (Permission $record) => $record->name ?: 'Permission #' . $record->id,
                'subtitle' => fn (Permission $record) => $record->table_name ?: 'No table mapping',
                'meta' => fn (?Permission $record = null) => 'Permission rule',
                'order_by' => [['name', 'asc']],
            ],
            [
                'key' => 'activity_logs',
                'label' => 'Activity Logs',
                'icon' => 'fa-clock-rotate-left',
                'model' => UserActivityLog::class,
                'menu_permissions' => ['ActivityLogs_Menu'],
                'view_permissions' => ['ActivityLogs_ViewAll', 'ActivityLogs_View'],
                'edit_permissions' => [],
                'routes' => ['index' => 'admin.activity-logs', 'show' => 'admin.activity-logs.show'],
                'searchable' => ['model_type', 'table_name', 'action'],
                'search_relations' => ['user' => ['name', 'email']],
                'title' => fn (UserActivityLog $record) => Str::headline($record->action ?: 'Activity'),
                'subtitle' => fn (UserActivityLog $record) => collect([$record->table_name, optional($record->user)->name])->filter()->implode(' | '),
                'meta' => fn (UserActivityLog $record) => optional($record->created_at)->format('d M Y, h:i A') ?: 'System log',
                'query' => function (Builder $query, User $user) {
                    $query->with('user');

                    if (!SystemVisibility::isSuperAdminUser($user)) {
                        $query->whereRaw('1 = 0');
                    }
                },
                'order_by' => [['created_at', 'desc']],
            ],
        ];
    }
}
