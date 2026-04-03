<?php

use Illuminate\Support\Facades\Route;
use Modules\TrPackage\Http\Controllers\EkspedisiController;
use Modules\TrPackage\Http\Controllers\TypePackageController;
use Modules\TrPackage\Http\Controllers\PickupPackageController;
use Modules\TrPackage\Http\Controllers\ReceivingPackageController;

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
    Route::get('receive/download/{id}', [ReceivingPackageController::class, 'download'])->name('receive.download');
    Route::get('receive/export', ['uses' => 'ReceivingPackageController@export'])->name('receive.export');
    Route::post('receive/apply-quick-action', [ReceivingPackageController::class, 'applyQuickAction'])->name('receive.apply_quick_action');
    Route::resource('receive', ReceivingPackageController::class);

    Route::get('pickup/download/{id}', [PickupPackageController::class, 'download'])->name('pickup.download');
    Route::get('pickup/export', ['uses' => 'PickupPackageController@export'])->name('pickup.export');
    Route::post('pickup/apply-quick-action', [PickupPackageController::class, 'applyQuickAction'])->name('pickup.apply_quick_action');
    Route::resource('pickup', PickupPackageController::class);
    Route::resource('ekspedisi', EkspedisiController::class);
    Route::resource('type', TypePackageController::class);
});
