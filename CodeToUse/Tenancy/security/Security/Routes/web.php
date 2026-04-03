<?php

use Illuminate\Support\Facades\Route;
use Modules\Security\Http\Controllers\SecurityController;
use Modules\Security\Http\Controllers\SecurityWPController;

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
    Route::get('security-transfer/download/{id}', [SecurityController::class, 'download'])->name('security-transfer.download');
    Route::get('security-transfer/export', ['uses' => 'SecurityController@export'])->name('security-transfer.export');
    Route::get('security-transfer/validate/{id}', [SecurityController::class, 'validateData'])->name('security-transfer.validate');
    Route::post('security-transfer/validated/{id}', [SecurityController::class, 'processValidatedData'])->name('security-transfer.validated');
    Route::resource('security-transfer', SecurityController::class);

    Route::get('security-workpermit/download/{id}', [SecurityWPController::class, 'download'])->name('security-workpermit.download');
    Route::get('security-workpermit/export', ['uses' => 'SecurityWPController@export'])->name('security-workpermit.export');
    Route::get('security-workpermit/validate/{id}', [SecurityWPController::class, 'validateData'])->name('security-workpermit.validate');
    Route::post('security-workpermit/validated/{id}', [SecurityWPController::class, 'processValidatedData'])->name('security-workpermit.validated');
    Route::resource('security-workpermit', SecurityWPController::class);
});
