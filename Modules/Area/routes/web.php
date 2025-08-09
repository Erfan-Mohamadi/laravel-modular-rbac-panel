<?php

use Illuminate\Support\Facades\Route;
use Modules\Area\Http\Controllers\AreaController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('areas', AreaController::class)->names('area');
});
