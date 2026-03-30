<?php

/* Setting menu routes starts from here */

use Illuminate\Support\Facades\Route;
use Modules\Units\Http\Controllers\FloorsController;
use Modules\Units\Http\Controllers\TowersController;
use Modules\Units\Http\Controllers\UnitSettingController;
use Modules\Units\Http\Controllers\TypeUnitsController;

Route::group(['middleware' => 'auth', 'prefix' => 'account/settings'], function () {



    /* Start Ticket settings routes */

    Route::resource('unit-settings', UnitSettingController::class);
    Route::resource('towers', TowersController::class);
    Route::resource('floors', FloorsController::class);
    Route::resource('typeunits', TypeUnitsController::class);




});

