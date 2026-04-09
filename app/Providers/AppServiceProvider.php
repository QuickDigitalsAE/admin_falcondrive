<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;

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

            return $user->roles()->where('id', 1)->exists() ? true : null;
        });
    }
}