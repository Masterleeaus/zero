<?php

/* Setting menu routes starts from here */

use Illuminate\Support\Facades\Route;
use Modules\Houses\Http\Controllers\AreasController;
use Modules\Houses\Http\Controllers\TowersController;
use Modules\Houses\Http\Controllers\HouseSettingController;
use Modules\Houses\Http\Controllers\TypeHousesController;

Route::group(['middleware' => 'auth', 'prefix' => 'account/settings'], function () {



    /* Start Ticket settings routes */

    Route::resource('house-settings', HouseSettingController::class);
    Route::resource('towers', TowersController::class);
    Route::resource('areas', AreasController::class);
    Route::resource('typehouses', TypeHousesController::class);




});

