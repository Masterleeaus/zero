<?php

use Modules\TrNotes\Http\Controllers\NotesController;

use Illuminate\Support\Facades\Route; //ini baru di tambahkan
use Modules\TrNotes\Http\Controllers\TenancySettingController;


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

    //region Units Routes

    Route::resource('notes', NotesController::class);
    Route::resource('tenancy-settings', TenancySettingController::class);

});
