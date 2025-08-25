<?php

use Illuminate\Support\Facades\Route;
use Modules\Area\Http\Controllers\Admin\CityController;
use Modules\Area\Http\Controllers\Admin\ProvinceController;

Route::middleware(['auth:admin'])->prefix('admin/area')->group(function () {
    Route::resource('cities', CityController::class)->names('cities');
    Route::get('provinces', [ProvinceController::class, 'index'])->name('provinces.index');
});
