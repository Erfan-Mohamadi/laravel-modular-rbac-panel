<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\API\CartController;


/*
|--------------------------------------------------------------------------
| Cart API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('customer')->middleware('auth:sanctum')->group(function () {

    // Cart management routes
    Route::get('cart', [CartController::class, 'index']);
    Route::post('cart', [CartController::class, 'store']);
    Route::get('cart/{id}', [CartController::class, 'show']);
    Route::put('cart/{id}', [CartController::class, 'update']);
    Route::delete('cart/{id}', [CartController::class, 'destroy']);

    // Additional cart operations
    Route::delete('cart/clear/all', [CartController::class, 'clear']); // Fixed route conflict
    Route::get('cart-summary', [CartController::class, 'summary']);
    Route::get('cart-count', [CartController::class, 'count']);
    Route::post('cart-sync', [CartController::class, 'sync']);
    Route::put('cart-bulk-update', [CartController::class, 'bulkUpdate']);

});
