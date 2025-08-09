<?php

use Illuminate\Support\Facades\Route;
use Modules\Area\Http\Controllers\CityController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('areas', CityController::class)->names('area');
});
