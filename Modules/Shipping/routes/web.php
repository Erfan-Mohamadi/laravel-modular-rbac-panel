<?php

use Illuminate\Support\Facades\Route;
use Modules\Shipping\Http\Controllers\Admin\ShippingController;

Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    Route::resource('shipping', ShippingController::class)->names('shipping');
});
