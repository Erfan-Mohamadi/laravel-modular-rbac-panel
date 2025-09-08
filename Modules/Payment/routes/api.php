<?php

use Illuminate\Support\Facades\Route;
use Modules\Payment\Http\Controllers\API\PaymentController;


/*
|--------------------------------------------------------------------------
| Order Module API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('customer')->middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Payment routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']); // Get payment history
        Route::get('/{id}', [PaymentController::class, 'show']); // Get payment details
        Route::post('/process/{invoiceId}', [PaymentController::class, 'processPayment']); // Process payment for invoice
        Route::post('/verify', [PaymentController::class, 'verifyPayment']); // Verify payment
    });
    /*
    |--------------------------------------------------------------------------
    | Public routes (no authentication required)
    |--------------------------------------------------------------------------
    */
    Route::prefix('payments')->group(function () {
        Route::post('/webhook', [PaymentController::class, 'webhook']); // Bank webhook callback
    });
});

