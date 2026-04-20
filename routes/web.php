<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AboutUsController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CarController;
use App\Http\Controllers\Admin\CarWithDriverController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\LeaseController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ForgotPasswordController;
use App\Http\Controllers\Admin\HighlightController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\GlobalSearchController;
use App\Http\Controllers\Admin\ResetPasswordController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\NotificationController;

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
    Route::get('/global-search', [GlobalSearchController::class, 'index'])->name('global-search');
    Route::get('/global-search/suggest', [GlobalSearchController::class, 'suggest'])->name('global-search.suggest');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

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

    Route::prefix('users')->group(function () {

        Route::get('/', [UserController::class, 'getUsers'])->name('users');
        Route::get('/create', [UserController::class, 'createUser'])->name('users.create');
        Route::post('/store', [UserController::class, 'postUser'])->name('users.store');
        Route::get('/{id}', [UserController::class, 'showUser'])->name('users.show');
        Route::get('/{id}/edit', [UserController::class, 'editUser'])->name('users.edit');
        Route::put('/{id}/update', [UserController::class, 'updateUser'])->name('users.update');        
        Route::delete('/{id}/delete', [UserController::class, 'deleteUser'])->name('users.delete');
        Route::put('/{id}/restore', [UserController::class, 'revokeUser'])->name('users.revoke');
        
        Route::put('/{id}/change-password', [UserController::class, 'changePassword'])->name('users.change-password');
    });

    Route::prefix('blogs')->name('blogs')->group(function () {
        Route::get('/', [BlogController::class, 'index'])->name('');
        Route::get('/create', [BlogController::class, 'createBlog'])->name('.create');
        Route::post('/store', [BlogController::class, 'postBlog'])->name('.store');
        Route::get('/show/{id}', [BlogController::class, 'showBlog'])->name('.show');
        Route::get('/edit/{id}', [BlogController::class, 'editBlog'])->name('.edit');
        Route::put('/update/{id}', [BlogController::class, 'updateBlog'])->name('.update');
        Route::delete('/delete/{id}', [BlogController::class, 'deleteBlog'])->name('.delete');
        Route::put('/restore/{id}', [BlogController::class, 'revokeBlog'])->name('.revoke');
    });

    Route::prefix('highlights')->name('highlights')->group(function () {
        Route::get('/', [HighlightController::class, 'index'])->name('');
        Route::get('/create', [HighlightController::class, 'create'])->name('.create');
        Route::post('/store', [HighlightController::class, 'store'])->name('.store');
        Route::get('/show/{id}', [HighlightController::class, 'show'])->name('.show');
        Route::get('/edit/{id}', [HighlightController::class, 'edit'])->name('.edit');
        Route::put('/update/{id}', [HighlightController::class, 'update'])->name('.update');
        Route::delete('/delete/{id}', [HighlightController::class, 'destroy'])->name('.delete');
        Route::put('/restore/{id}', [HighlightController::class, 'restore'])->name('.restore');
    });

    Route::prefix('inquiries')->name('inquiries')->group(function () {
        Route::get('/', [InquiryController::class, 'index'])->name('');
        Route::get('/create', [InquiryController::class, 'create'])->name('.create');
        Route::post('/store', [InquiryController::class, 'store'])->name('.store');
        Route::get('/show/{id}', [InquiryController::class, 'show'])->name('.show');
        Route::get('/edit/{id}', [InquiryController::class, 'edit'])->name('.edit');
        Route::put('/update/{id}', [InquiryController::class, 'update'])->name('.update');
        Route::delete('/delete/{id}', [InquiryController::class, 'destroy'])->name('.delete');
        Route::put('/restore/{id}', [InquiryController::class, 'restore'])->name('.restore');
    });

    Route::prefix('cars')->name('cars')->group(function () {
        Route::get('/', [CarController::class, 'index'])->name('');
        Route::get('/create', [CarController::class, 'createCar'])->name('.create');
        Route::get('/sort-orders/{brandId}', [CarController::class, 'getSortOrders'])->name('.sort-orders');
        Route::get('/featured-sort-orders', [CarController::class, 'getFeaturedSortOrders'])->name('.featured-sort-orders');
        Route::post('/store', [CarController::class, 'postCar'])->name('.store');
        Route::get('/show/{id}', [CarController::class, 'showCar'])->name('.show');
        Route::get('/edit/{id}', [CarController::class, 'editCar'])->name('.edit');
        Route::put('/update/{id}', [CarController::class, 'updateCar'])->name('.update');
        Route::delete('/delete/{id}', [CarController::class, 'deleteCar'])->name('.delete');
        Route::put('/restore/{id}', [CarController::class, 'revokeCar'])->name('.revoke');
    });

    Route::prefix('car-with-drivers')->name('car-with-drivers')->group(function () {
        Route::get('/', [CarWithDriverController::class, 'index'])->name('');
        Route::get('/create', [CarWithDriverController::class, 'create'])->name('.create');
        Route::post('/store', [CarWithDriverController::class, 'store'])->name('.store');
        Route::get('/show/{id}', [CarWithDriverController::class, 'show'])->name('.show');
        Route::get('/edit/{id}', [CarWithDriverController::class, 'edit'])->name('.edit');
        Route::put('/update/{id}', [CarWithDriverController::class, 'update'])->name('.update');
        Route::delete('/delete/{id}', [CarWithDriverController::class, 'destroy'])->name('.delete');
        Route::put('/restore/{id}', [CarWithDriverController::class, 'restore'])->name('.restore');
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

    Route::prefix('promotions')->name('promotions')->group(function () {
        Route::get('/', [PromotionController::class, 'index'])->name('');
        Route::get('/create', [PromotionController::class, 'create'])->name('.create');
        Route::post('/store', [PromotionController::class, 'store'])->name('.store');
        Route::get('/show/{id}', [PromotionController::class, 'show'])->name('.show');
        Route::get('/edit/{id}', [PromotionController::class, 'edit'])->name('.edit');
        Route::put('/update/{id}', [PromotionController::class, 'update'])->name('.update');
        Route::delete('/delete/{id}', [PromotionController::class, 'destroy'])->name('.delete');
        Route::put('/restore/{id}', [PromotionController::class, 'restore'])->name('.restore');
    });

    Route::prefix('settings')->name('settings')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('');
        Route::get('/create', [SettingController::class, 'create'])->name('.create');
        Route::post('/store', [SettingController::class, 'store'])->name('.store');
        Route::get('/show/{id}', [SettingController::class, 'show'])->name('.show');
        Route::get('/edit/{id}', [SettingController::class, 'edit'])->name('.edit');
        Route::put('/update/{id}', [SettingController::class, 'update'])->name('.update');
        Route::delete('/delete/{id}', [SettingController::class, 'destroy'])->name('.delete');
        Route::put('/restore/{id}', [SettingController::class, 'restore'])->name('.restore');
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

    Route::prefix('activity-logs')->name('activity-logs')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('');
        Route::get('/{id}', [ActivityLogController::class, 'show'])->name('.show');
    });
});
