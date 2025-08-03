<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Admin\AuthController;

Route::middleware(['guest'])->prefix('admin')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('showLoginForm');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
