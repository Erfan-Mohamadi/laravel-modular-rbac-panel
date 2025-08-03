<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\Admin\CustomerController;

Route::middleware(['auth:admin', 'verified'])->group(function () {
    Route::resource('customers', CustomerController::class)
        ->names('customer')
        ->middlewareFor(['index', 'show'], 'can:view customers')
        ->middlewareFor(['create', 'store'], 'can:create customers');

});