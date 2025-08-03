<?php

use Illuminate\Support\Facades\Route;
use Modules\Permission\Http\Controllers\admin\PermissionController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('permissions', PermissionController::class)->names('permission');
});
