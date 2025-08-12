<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\Admin\AttributeItemController;
use Modules\Product\Http\Controllers\Admin\BrandController;
use Modules\Product\Http\Controllers\Admin\AttributeController;
use Modules\Product\Http\Controllers\Admin\SpecialtyController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    Route::resource('brands', BrandController::class)->names('brands');
    Route::resource('attributes', AttributeController::class)->names('attributes');

    // Nested resource for attribute items
    Route::resource('attributes.items', AttributeItemController::class)->names('attributes.items');
    Route::post('attributes/create', [AttributeController::class, 'storeMultiple']);
    // Custom route for bulk store
    Route::post('attributes/{attribute}/items/store-multiple', [AttributeItemController::class, 'storeMultiple'])
        ->name('attributes.items.store-multiple');

    Route::resource('specialties', SpecialtyController::class);
    Route::get('specialties/{specialty}/items', [SpecialtyController::class, 'getSpecialtyItems'])
        ->name('specialties.items');
});
