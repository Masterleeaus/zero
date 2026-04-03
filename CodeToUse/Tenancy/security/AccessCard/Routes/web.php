<?php

use Illuminate\Support\Facades\Route;
use Modules\TrAccessCard\Http\Controllers\CardAccessController;

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
    Route::get('card-access/download/{id}', [CardAccessController::class, 'download'])->name('card-access.download');
    Route::get('card-access/export', ['uses' => 'CardAccessController@export'])->name('card-access.export');
    Route::post('card-access/apply-quick-action', [CardAccessController::class, 'applyQuickAction'])->name('card-access.apply_quick_action');
    Route::get('card-access/client', [CardAccessController::class, 'client'])->name('card-access.client');
    Route::resource('card-access', CardAccessController::class);
});
