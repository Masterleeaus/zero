<?php

use Modules\Houses\Http\Controllers\HousesController;

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

                //region Houses Routes
                Route::get('houses/export', ['uses' => 'HousesController@export'])->name('houses.export');
                Route::post('houses/apply-quick-action', [HousesController::class, 'applyQuickAction'])->name('houses.apply_quick_action');
                Route::resource('houses', HousesController::class);

                //endregion

                // Route::group(
                //     ['prefix' => 'settings'],
                //     function () {

                //         Route::get('areas/createModal', ['uses' => 'AreasController@createModal'])->name('areas.createModal');
                //         Route::resource('areas', 'AreasController');

                //         Route::get('towers/createModal', ['uses' => 'TowersController@createModal'])->name('towers.createModal');
                //         Route::resource('towers', 'TowersController');

                //         Route::get('typeHouses/createModal', ['uses' => 'TypeHousesController@createModal'])->name('typeHouses.createModal');
                //         Route::resource('typeHouses', 'TypeHousesController');


                //     }
                // );






});
