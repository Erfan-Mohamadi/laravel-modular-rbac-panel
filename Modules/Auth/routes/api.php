<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\API\Customer\AuthController;

Route::prefix('customer')->group(function () {
    Route::post('send-otp', [AuthController::class, 'sendOtp']);
    Route::post('verify-otp-register', [AuthController::class, 'verifyOtpAndRegister']);
    Route::post('resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});
