<?php

use Illuminate\Support\Facades\Route;
use Modules\Category\Http\Controllers\Admin\CategoryController;
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('categories', CategoryController::class)->names('category');
});
