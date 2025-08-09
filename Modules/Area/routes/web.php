<?php

use Illuminate\Support\Facades\Route;
use Modules\Area\Http\Controllers\Admin\CityController;

Route::middleware(['auth:admin'])->prefix('admin/area')->group(function () {
    Route::resource('cities', CityController::class)->names('cities');
});
