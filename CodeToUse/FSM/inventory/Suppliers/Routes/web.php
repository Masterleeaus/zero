<?php

use Illuminate\Support\Facades\Route;
use Modules\Suppliers\Http\Controllers\SupplierController;

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

    Route::get('suppliers/export', ['uses' => 'SupplierController@export'])->name('suppliers.export');
    Route::post('suppliers/apply-quick-action', [SupplierController::class, 'applyQuickAction'])->name('suppliers.apply_quick_action');
    Route::resource('suppliers', SupplierController::class);
});
