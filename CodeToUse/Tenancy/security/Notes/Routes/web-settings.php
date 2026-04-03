<?php

/* Setting menu routes starts from here */

use Illuminate\Support\Facades\Route;
use Modules\TrNotes\Http\Controllers\TenancySettingController;

Route::group(['middleware' => 'auth', 'prefix' => 'account/settings'], function () {
    /* Start Ticket settings routes */
    Route::resource('tenancy-settings', TenancySettingController::class);
});
