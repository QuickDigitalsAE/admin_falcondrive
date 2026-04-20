<?php

namespace App\Providers;

use App\Models\UserActivityLog;
use App\Support\SystemVisibility;
use App\Support\AdminNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        /**
         * Super Administrator Full Access by Role ID = 1
         *
         * If logged in user has role ID 1
         * then allow all permissions automatically
         * even if permissions are not assigned.
         */
        Gate::before(function ($user, $ability) {
            if (!$user) {
                return null;
            }

            return $user->roles()->where('id', SystemVisibility::superAdminRoleId())->exists() ? true : null;
        });

        UserActivityLog::created(function (UserActivityLog $log) {
            AdminNotificationService::notifyFromActivityLog($log);
        });

        View::composer('admin.layouts.partials.navbar', function ($view) {
            $user = Auth::user();

            if (!$user) {
                $view->with('navbarNotifications', collect())
                    ->with('navbarUnreadNotificationsCount', 0);
                return;
            }

            $view->with(
                'navbarNotifications',
                $user->adminNotifications()
                    ->unread()
                    ->latest()
                    ->get()
                    ->filter(fn ($notification) => AdminNotificationService::canUserViewNotification($user, $notification))
                    ->take(8)
                    ->map(fn ($notification) => AdminNotificationService::formatForUi($notification))
            )
                ->with(
                    'navbarUnreadNotificationsCount',
                    $user->adminNotifications()
                        ->unread()
                        ->get()
                        ->filter(fn ($notification) => AdminNotificationService::canUserViewNotification($user, $notification))
                        ->count()
                );
        });
    }
}
