<?php

use Illuminate\Support\Facades\Route;
use Modules\Area\Http\Controllers\AreaController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('areas', AreaController::class)->names('area');
});
