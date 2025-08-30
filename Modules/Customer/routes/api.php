<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\API\CustomerController;
use Modules\Customer\Http\Controllers\API\AddressController;
use Modules\Customer\Http\Controllers\API\LocationController;

// Location routes (public - no auth needed)
Route::prefix('customer')->group(function () {
    Route::get('provinces', [LocationController::class, 'provinces']);
    Route::get('provinces/{provinceId}/cities', [LocationController::class, 'cities']);
    Route::get('cities', [LocationController::class, 'allCities']);
});

Route::prefix('customer')->middleware('auth:sanctum')->group(function () {
    // Customer profile routes
    Route::get('profile', [CustomerController::class, 'profile']);
    Route::put('profile', [CustomerController::class, 'updateProfile']);

    // Address management routes
    Route::apiResource('addresses', AddressController::class);
});
