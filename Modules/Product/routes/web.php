<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\Admin\AttributeItemController;
use Modules\Product\Http\Controllers\Admin\BrandController;
use Modules\Product\Http\Controllers\Admin\AttributeController;
use Modules\Product\Http\Controllers\Admin\ProductController;
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

    Route::resource('products', ProductController::class)->names('products');
    Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
        ->name('products.toggle-status');
    Route::patch('products/{product}/change-availability', [ProductController::class, 'changeAvailabilityStatus'])
        ->name('products.change-availability');

    Route::get('/admin/products/specialties-by-category', [ProductController::class, 'getSpecialtiesByCategory'])
        ->name('products.specialties.byCategory');

    Route::get('/brands-by-category', [ProductController::class, 'brandsByCategory'])->name('brands.byCategory');

});
