<?php

use Illuminate\Support\Facades\Route;
use Modules\Category\Http\Controllers\Admin\CategoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:admin', 'role:super_admin'])->prefix('admin')->group(function () {
    Route::resource('categories', CategoryController::class)->names('categories');
});
