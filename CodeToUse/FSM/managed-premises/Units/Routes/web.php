<?php

use Illuminate\Support\Facades\Route;
use Modules\Units\Http\Controllers\UnitsConfigurationController;
use Modules\Units\Http\Controllers\UnitsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => 'auth', 'prefix' => 'account'], function () {
    Route::post('units/apply-quick-action', [UnitsController::class, 'applyQuickAction'])->name('units.apply_quick_action');
    Route::resource('units', UnitsController::class);

    Route::resource('units-configuration', UnitsConfigurationController::class);
});
