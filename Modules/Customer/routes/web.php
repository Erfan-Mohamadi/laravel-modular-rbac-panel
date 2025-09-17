<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\Admin\CustomerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/


Route::middleware(['auth:admin'])->prefix('admin')->group(function () {

    Route::post('customers/{customer}/restore', [CustomerController::class, 'restore'])->name('customers.restore');
    Route::resource('customers', CustomerController::class)->names('customers');
});
