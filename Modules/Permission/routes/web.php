<?php

use Illuminate\Support\Facades\Route;
use Modules\Permission\Http\Controllers\admin\RoleController;


Route::middleware(['auth:admin', 'role:super_admin'])->prefix('admin')->group(function () {
    Route::resource('roles', RoleController::class)->names('roles');
});

