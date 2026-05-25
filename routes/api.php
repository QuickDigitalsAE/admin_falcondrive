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
use App\Http\Controllers\APIs\FooterController;
use App\Http\Controllers\APIs\HeaderController;
use App\Http\Controllers\APIs\HighlightController;
use App\Http\Controllers\APIs\HomeController;
use App\Http\Controllers\APIs\InquiryController;
use App\Http\Controllers\APIs\BookingController;
use App\Http\Controllers\APIs\LeaseController;
use App\Http\Controllers\APIs\LocationController;
use App\Http\Controllers\APIs\PromotionController;
use App\Http\Controllers\APIs\SettingController;
use App\Http\Controllers\APIs\TestimonialController;
use App\Http\Controllers\Admin\PromoCodeController;

Route::prefix('speed')->group(function () {
    Route::get('/getVehicles', [InquiryController::class, 'getVehicles']);
    Route::get('/getVehicleGroups', [InquiryController::class, 'GetVehicleGroups']);
    Route::get('/getLocations', [InquiryController::class, 'GetLocations']);
    Route::get('/getChargesSettings', [InquiryController::class, 'GetChargesSettings']);
    Route::post('/getCustomerDetailByEmailOrMobileNo', [InquiryController::class, 'GetCustomerDetailByEmailOrMobileNo'])->name('get.customer.by.email');
    Route::post('/send-booking', [InquiryController::class, 'createBooking'])->name('send.booking');
    Route::post('/create-customer', [InquiryController::class, 'createCustomer'])->name('create.customer');
});

Route::prefix('website')->group(function () {
    // Public APIs
    Route::get('/home', HomeController::class);
    Route::get('/header', HeaderController::class);
    Route::get('/footer', FooterController::class);

    Route::get('/about-us', [AboutUsController::class, 'publicIndex']);
    Route::get('/blogs', [BlogController::class, 'publicIndex']);
    Route::get('/blogs/{blog:slug}', [BlogController::class, 'publicShow']);
    Route::get('/brands', [BrandController::class, 'publicIndex']);
    Route::get('/brands/{brand:slug}', [BrandController::class, 'publicShow']);
    Route::get('/our-fleet', [CarController::class, 'publicIndex']);
    Route::get('/our-fleet/{car:slug}', [CarController::class, 'publicShow']);
    Route::get('/cars-with-driver', [CarWithDriverController::class, 'publicIndex']);
    Route::get('/cars-with-driver/{carWithDriver:slug}', [CarWithDriverController::class, 'publicShow']);
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
    Route::post('/inquiries', [InquiryController::class, 'storePublic'])->middleware('throttle:5,1');

    Route::post('/bookings', [BookingController::class, 'storePublic'])->middleware('throttle:5,1');
    
    Route::post('/promo-codes/apply', [PromoCodeController::class, 'apply'])->name('api.promo-codes.apply');
});
