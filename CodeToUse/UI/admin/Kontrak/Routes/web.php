<?php

use Illuminate\Support\Facades\Route; //ini baru di tambahkan
use Modules\Kontrak\Http\Controllers\RecurringKontrakController;

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
    Route::get('kontrak/export', ['uses' => 'RecurringKontrakController@export'])->name('kontrak.export');
    Route::post('kontrak/apply-quick-action', [RecurringKontrakController::class, 'applyQuickAction'])->name('kontrak.apply_quick_action');
    Route::post('kontrak/change-status', [RecurringKontrakController::class, 'changeStatus'])->name('kontrak.change_status');
    Route::resource('kontrak', RecurringKontrakController::class);;
});
