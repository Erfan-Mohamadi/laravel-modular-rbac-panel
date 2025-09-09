<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\API\CartController;
use Modules\Order\Http\Controllers\API\OrderController;

/*
|--------------------------------------------------------------------------
| Order Module API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('customer')->middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Cart Management Routes
    |--------------------------------------------------------------------------
    */
    Route::get('cart', [CartController::class, 'index']);
    Route::post('cart', [CartController::class, 'store']);
    Route::get('cart/{id}', [CartController::class, 'show']);
    Route::put('cart/{id}', [CartController::class, 'update']);
    Route::delete('cart/{id}', [CartController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Order Management Routes
    |--------------------------------------------------------------------------
    */
    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{id}', [OrderController::class, 'show']);
    Route::put('orders/{id}', [OrderController::class, 'update']);
    Route::delete('orders/{id}', [OrderController::class, 'destroy']);
    Route::post('orders/{id}/retry-payment', [OrderController::class, 'retryPayment']);
    Route::get('orders/{id}/payment-status', [OrderController::class, 'paymentStatus']);
    /*
    |--------------------------------------------------------------------------
    | Additional Order Routes
    |--------------------------------------------------------------------------
    */
    // Get orders by status
    Route::get('orders/status/{status}', [OrderController::class, 'getByStatus']);

    // Get order statistics for customer
    Route::get('orders-statistics', [OrderController::class, 'getStatistics']);
});

/*
|--------------------------------------------------------------------------
| Alternative RESTful Resource Route (if you prefer)
|--------------------------------------------------------------------------
|
| You can replace the individual order routes above with this single line:
| Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
|
*/
