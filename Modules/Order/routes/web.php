<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\Admin\OrderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    // Resource routes for orders
    Route::resource('orders', OrderController::class)->names('orders');

    // Additional custom routes
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::patch('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('orders/export', [OrderController::class, 'export'])->name('orders.export');
});
