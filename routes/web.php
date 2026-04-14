<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AboutUsController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\LeaseController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TestimonialController;
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

    Route::prefix('about-us')->name('about-us')->group(function () {
        Route::get('/', [AboutUsController::class, 'index'])->name('');
        Route::get('/create', [AboutUsController::class, 'create'])->name('.create');
        Route::post('/store', [AboutUsController::class, 'store'])->name('.store');
        Route::get('/{id}', [AboutUsController::class, 'show'])->name('.show');
        Route::get('/{id}/edit', [AboutUsController::class, 'edit'])->name('.edit');
        Route::put('/{id}/update', [AboutUsController::class, 'update'])->name('.update');
        Route::delete('/{id}/delete', [AboutUsController::class, 'destroy'])->name('.delete');
        Route::put('/{id}/restore', [AboutUsController::class, 'restore'])->name('.restore');
    });

    Route::prefix('brands')->name('brands')->group(function () {
        Route::get('/', [BrandController::class, 'index'])->name('');
        Route::get('/create', [BrandController::class, 'create'])->name('.create');
        Route::post('/store', [BrandController::class, 'store'])->name('.store');
        Route::get('/{id}', [BrandController::class, 'show'])->name('.show');
        Route::get('/{id}/edit', [BrandController::class, 'edit'])->name('.edit');
        Route::put('/{id}/update', [BrandController::class, 'update'])->name('.update');
        Route::delete('/{id}/delete', [BrandController::class, 'destroy'])->name('.delete');
        Route::put('/{id}/restore', [BrandController::class, 'restore'])->name('.restore');
    });

    Route::prefix('categories')->name('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('');
        Route::get('/create', [CategoryController::class, 'create'])->name('.create');
        Route::post('/store', [CategoryController::class, 'store'])->name('.store');
        Route::get('/{id}', [CategoryController::class, 'show'])->name('.show');
        Route::get('/{id}/edit', [CategoryController::class, 'edit'])->name('.edit');
        Route::put('/{id}/update', [CategoryController::class, 'update'])->name('.update');
        Route::delete('/{id}/delete', [CategoryController::class, 'destroy'])->name('.delete');
        Route::put('/{id}/restore', [CategoryController::class, 'restore'])->name('.restore');
    });

    Route::prefix('faq')->name('faq')->group(function () {
        Route::get('/', [FaqController::class, 'index'])->name('');
        Route::get('/create', [FaqController::class, 'create'])->name('.create');
        Route::post('/store', [FaqController::class, 'store'])->name('.store');
        Route::get('/{id}', [FaqController::class, 'show'])->name('.show');
        Route::get('/{id}/edit', [FaqController::class, 'edit'])->name('.edit');
        Route::put('/{id}/update', [FaqController::class, 'update'])->name('.update');
        Route::delete('/{id}/delete', [FaqController::class, 'destroy'])->name('.delete');
        Route::put('/{id}/restore', [FaqController::class, 'restore'])->name('.restore');
    });

    Route::prefix('lease')->name('lease')->group(function () {
        Route::get('/', [LeaseController::class, 'index'])->name('');
        Route::get('/create', [LeaseController::class, 'create'])->name('.create');
        Route::post('/store', [LeaseController::class, 'store'])->name('.store');
        Route::get('/{id}', [LeaseController::class, 'show'])->name('.show');
        Route::get('/{id}/edit', [LeaseController::class, 'edit'])->name('.edit');
        Route::put('/{id}/update', [LeaseController::class, 'update'])->name('.update');
        Route::delete('/{id}/delete', [LeaseController::class, 'destroy'])->name('.delete');
        Route::put('/{id}/restore', [LeaseController::class, 'restore'])->name('.restore');
    });

    Route::prefix('locations')->name('locations')->group(function () {
        Route::get('/', [LocationController::class, 'index'])->name('');
        Route::get('/create', [LocationController::class, 'create'])->name('.create');
        Route::post('/store', [LocationController::class, 'store'])->name('.store');
        Route::get('/{id}', [LocationController::class, 'show'])->name('.show');
        Route::get('/{id}/edit', [LocationController::class, 'edit'])->name('.edit');
        Route::put('/{id}/update', [LocationController::class, 'update'])->name('.update');
        Route::delete('/{id}/delete', [LocationController::class, 'destroy'])->name('.delete');
        Route::put('/{id}/restore', [LocationController::class, 'restore'])->name('.restore');
    });

    Route::prefix('testimonials')->name('testimonials')->group(function () {
        Route::get('/', [TestimonialController::class, 'index'])->name('');
        Route::get('/create', [TestimonialController::class, 'create'])->name('.create');
        Route::post('/store', [TestimonialController::class, 'store'])->name('.store');
        Route::get('/{id}', [TestimonialController::class, 'show'])->name('.show');
        Route::get('/{id}/edit', [TestimonialController::class, 'edit'])->name('.edit');
        Route::put('/{id}/update', [TestimonialController::class, 'update'])->name('.update');
        Route::delete('/{id}/delete', [TestimonialController::class, 'destroy'])->name('.delete');
        Route::put('/{id}/restore', [TestimonialController::class, 'restore'])->name('.restore');
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
