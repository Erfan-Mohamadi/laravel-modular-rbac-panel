<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\Admin\BrandController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    Route::resource('brands', BrandController::class)->names('brands');
});
