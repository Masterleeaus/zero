<?php

use Illuminate\Support\Facades\Route;
use Modules\TrInOutPermit\Http\Controllers\TrInOutPermitPermissionController;

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
    Route::get('trinoutpermit/download/{id}', [TrInOutPermitPermissionController::class, 'download'])->name('trinoutpermit.download');
    Route::get('trinoutpermit/export', ['uses' => 'TrInOutPermitPermissionController@export'])->name('trinoutpermit.export');
    Route::post('trinoutpermit/apply-quick-action', [TrInOutPermitPermissionController::class, 'applyQuickAction'])->name('trinoutpermit.apply_quick_action');
    Route::get('trinoutpermit/client', [TrInOutPermitPermissionController::class, 'client'])->name('trinoutpermit.client');
    Route::get('trinoutpermit/approved/{id}', [TrInOutPermitPermissionController::class, 'approved'])->name('trinoutpermit.approved');
    Route::get('trinoutpermit/approved_bm/{id}', [TrInOutPermitPermissionController::class, 'approved_bm'])->name('trinoutpermit.approved_bm');
    Route::get('trinoutpermit/validate/{id}', [TrInOutPermitPermissionController::class, 'validateData'])->name('trinoutpermit.validate');
    Route::post('trinoutpermit/validated/{id}', [TrInOutPermitPermissionController::class, 'processValidatedData'])->name('trinoutpermit.validated');
    Route::resource('trinoutpermit', TrInOutPermitPermissionController::class);
});
