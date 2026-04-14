<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ForgotPasswordController;
use App\Http\Controllers\Admin\ResetPasswordController;

/*
|--------------------------------------------------------------------------
| Root URL
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('login');
})->name('home');


/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
        ->name('password.request');

    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
        ->name('password.reset');

    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
        ->name('password.update');
});


/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active.user'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/profile', [AccountController::class, 'editProfile'])->name('profile');
        Route::put('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');

        Route::get('/settings', [AccountController::class, 'editSettings'])->name('settings');
        Route::put('/settings/password', [AccountController::class, 'updatePassword'])->name('settings.password');
    });

    Route::prefix('roles')->name('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('');
        Route::get('/create', [RoleController::class, 'create'])->name('.create');
        Route::post('/store', [RoleController::class, 'store'])->name('.store');
        Route::get('/{id}', [RoleController::class, 'show'])->name('.show');
        Route::get('/{id}/edit', [RoleController::class, 'edit'])->name('.edit');
        Route::put('/{id}/update', [RoleController::class, 'update'])->name('.update');
        Route::delete('/{id}/delete', [RoleController::class, 'destroy'])->name('.delete');
        Route::put('/{id}/restore', [RoleController::class, 'restore'])->name('.restore');
    });

    Route::prefix('permissions')->name('permissions')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->name('');
        Route::get('/create', [PermissionController::class, 'create'])->name('.create');
        Route::post('/store', [PermissionController::class, 'store'])->name('.store');
        Route::get('/{id}', [PermissionController::class, 'show'])->name('.show');
        Route::get('/{id}/edit', [PermissionController::class, 'edit'])->name('.edit');
        Route::put('/{id}/update', [PermissionController::class, 'update'])->name('.update');
        Route::delete('/{id}/delete', [PermissionController::class, 'destroy'])->name('.delete');
        Route::put('/{id}/restore', [PermissionController::class, 'restore'])->name('.restore');
    });

    Route::prefix('users')->group(function () {

        Route::get('/', [UserController::class, 'getUsers'])->name('users');

        Route::get('/create', [UserController::class, 'createUser'])->name('users.create');
        Route::post('/store', [UserController::class, 'postUser'])->name('users.store');

        Route::get('/{id}', [UserController::class, 'showUser'])->name('users.show');
        Route::get('/{id}/edit', [UserController::class, 'editUser'])->name('users.edit');
        Route::put('/{id}/update', [UserController::class, 'updateUser'])->name('users.update');

        Route::put('/{id}/change-password', [UserController::class, 'changePassword'])->name('users.change-password');

        Route::delete('/{id}/delete', [UserController::class, 'deleteUser'])->name('users.delete');
        Route::put('/{id}/restore', [UserController::class, 'revokeUser'])->name('users.revoke');
    });

    Route::prefix('activity-logs')->name('activity-logs')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('');
        Route::get('/{id}', [ActivityLogController::class, 'show'])->name('.show');
    });
});
