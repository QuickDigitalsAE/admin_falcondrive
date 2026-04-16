<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIs\AuthController;
use App\Http\Controllers\APIs\AboutUsController;
use App\Http\Controllers\APIs\BlogController;
use App\Http\Controllers\APIs\BrandController;
use App\Http\Controllers\APIs\CarController;
use App\Http\Controllers\APIs\CarWithDriverController;
use App\Http\Controllers\APIs\CategoryController;
use App\Http\Controllers\APIs\FaqController;
use App\Http\Controllers\APIs\HighlightController;
use App\Http\Controllers\APIs\HomeController;
use App\Http\Controllers\APIs\InquiryController;
use App\Http\Controllers\APIs\LeaseController;
use App\Http\Controllers\APIs\LocationController;
use App\Http\Controllers\APIs\PromotionController;
use App\Http\Controllers\APIs\SettingController;
use App\Http\Controllers\APIs\TestimonialController;

Route::prefix('v1')->group(function () {
    // Public APIs
    Route::get('/home', HomeController::class);

    Route::get('/about-us', [AboutUsController::class, 'publicIndex']);
    Route::get('/blogs', [BlogController::class, 'publicIndex']);
    Route::get('/blogs/{blog:slug}', [BlogController::class, 'publicShow']);
    Route::get('/brands', [BrandController::class, 'publicIndex']);
    Route::get('/brands/{brand:slug}', [BrandController::class, 'publicShow']);
    Route::get('/cars', [CarController::class, 'publicIndex']);
    Route::get('/cars/{car:slug}', [CarController::class, 'publicShow']);
    Route::get('/cars-with-driver', [CarWithDriverController::class, 'publicIndex']);
    Route::get('/cars-with-driver/{carWithDriver}', [CarWithDriverController::class, 'publicShow']);
    Route::get('/categories', [CategoryController::class, 'publicIndex']);
    Route::get('/categories/{category:slug}', [CategoryController::class, 'publicShow']);
    Route::get('/faqs', [FaqController::class, 'publicIndex']);
    Route::get('/highlights', [HighlightController::class, 'publicIndex']);
    Route::get('/lease', [LeaseController::class, 'publicIndex']);
    Route::get('/lease/{lease:slug}', [LeaseController::class, 'publicShow']);
    Route::get('/locations', [LocationController::class, 'publicIndex']);
    Route::get('/locations/{location:slug}', [LocationController::class, 'publicShow']);
    Route::get('/promotions', [PromotionController::class, 'publicIndex']);
    Route::get('/promotions/{promotion:slug}', [PromotionController::class, 'publicShow']);
    Route::get('/settings', [SettingController::class, 'publicIndex']);
    Route::get('/testimonials', [TestimonialController::class, 'publicIndex']);
    Route::post('/inquiries', [InquiryController::class, 'storePublic']);
    
});
