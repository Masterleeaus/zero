<?php

/* Setting menu routes starts from here */

use Illuminate\Support\Facades\Route;
// use Modules\Accountings\Http\Controllers\UnitSettingController;
// use Modules\Accountings\Http\Controllers\BalanceSheetController;
// use Modules\Accountings\Http\Controllers\PnlController;

Route::group(['middleware' => 'auth', 'prefix' => 'kontrak/settings'], function () {
    /* Start Ticket settings routes */
    // Route::resource('acc-settings', UnitSettingController::class);
    // Route::resource('balance-sheet', BalanceSheetController::class);
    // Route::resource('pnl', PnlController::class);
});
