<?php

use Illuminate\Support\Facades\Route;
use Modules\Store\Http\Controllers\Admin\StoreController;

Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    Route::resource('stores', StoreController::class)->names('stores');
    Route::post('/stores/transaction', [StoreController::class, 'transaction'])->name('stores.transaction');
});
