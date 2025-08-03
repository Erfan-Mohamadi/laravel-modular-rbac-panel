<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\DashboardController;

Route::middleware(['auth:admin', 'verified'])->group(function () {
    Route::get('admin/', [DashboardController::class, 'index'])->name('admin.dashboard');
});
