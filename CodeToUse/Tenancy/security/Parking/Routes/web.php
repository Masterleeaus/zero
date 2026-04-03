<?php

use Illuminate\Support\Facades\Route;
use Modules\Parking\Http\Controllers\ParkingController;

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
    Route::get('parking/download/{id}', [ParkingController::class, 'download'])->name('parking.download');    
    Route::get('parking/export', ['uses' => 'ParkingController@export'])->name('parking.export');
    Route::post('parking/apply-quick-action', [ParkingController::class, 'applyQuickAction'])->name('parking.apply_quick_action');
    Route::resource('parking', ParkingController::class);
});
