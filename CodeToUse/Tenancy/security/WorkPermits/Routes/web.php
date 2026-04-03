<?php

use Illuminate\Support\Facades\Route;
use Modules\TrWorkPermits\Http\Controllers\WorkPermitsController;
use Modules\TrWorkPermits\Http\Controllers\WorkPermitsFileController;

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
    Route::get('work-permits/download/{id}', [WorkPermitsController::class, 'download'])->name('work-permits.download');
    Route::get('work-permits/export', ['uses' => 'WorkPermitsController@export'])->name('work-permits.export');
    Route::post('work-permits/apply-quick-action', [WorkPermitsController::class, 'applyQuickAction'])->name('work-permits.apply_quick_action');
    Route::get('work-permits/client', [WorkPermitsController::class, 'client'])->name('work-permits.client');
    Route::get('work-permits/approved/{id}', [WorkPermitsController::class, 'approved'])->name('work-permits.approved');
    Route::get('work-permits/approved_bm/{id}', [WorkPermitsController::class, 'approved_bm'])->name('work-permits.approved_bm');
    Route::get('work-permits/validate/{id}', [WorkPermitsController::class, 'validateData'])->name('work-permits.validate');
    Route::post('work-permits/validated/{id}', [WorkPermitsController::class, 'processValidatedData'])->name('work-permits.validated');
    Route::resource('work-permits', WorkPermitsController::class);
    Route::post('work-permits-file/multiple-upload', [WorkPermitsFileController::class, 'storeMultiple'])->name('work-permits-file.multiple_upload');
    Route::resource('work-permits-file', WorkPermitsFileController::class);
});
