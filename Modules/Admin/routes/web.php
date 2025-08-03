<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\Admin\AdminController;

Route::middleware(['auth:admin', 'role:super_admin'])->prefix('admin')->group(function () {
    Route::resource('admins', AdminController::class)->names('admin');
});
