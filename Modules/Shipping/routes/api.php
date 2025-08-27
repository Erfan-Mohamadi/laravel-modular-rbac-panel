<?php

use Illuminate\Support\Facades\Route;
use Modules\Shipping\Http\Controllers\Admin\ShippingController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('shippings', ShippingController::class)->names('shipping');
});
