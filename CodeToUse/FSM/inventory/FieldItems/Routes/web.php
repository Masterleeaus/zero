<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use Modules\FieldItems\Http\Controllers\ItemsController;
use Modules\FieldItems\Http\Controllers\ItemFileController;
use Modules\FieldItems\Http\Controllers\ItemCategoryController;
use Modules\FieldItems\Http\Controllers\ItemSubCategoryController;
use Modules\FieldItems\Http\Controllers\ItemPricingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider within a group
| which contains the "web" middleware group.
|
*/

Route::group(['middleware' => 'auth', 'prefix' => 'account'], function () {
    Route::post('items/apply-quick-action', [ProductController::class, 'applyQuickAction'])
        ->name('fielditems.apply_quick_action');

    Route::resource('items', ItemsController::class);
    Route::resource('itemCategory', ItemCategoryController::class);
    Route::get('getItemSubCategories/{id}', [ItemSubCategoryController::class, 'getSubCategories'])
        ->name('get_item_sub_categories');
    Route::resource('itemSubCategory', ItemSubCategoryController::class);

    Route::get('item-files/download/{id}', [ItemFileController::class, 'download'])
        ->name('item-files.download');
    Route::post('item-files/delete-image/{id}', [ItemFileController::class, 'deleteImage'])
        ->name('item-files.delete_image');
    Route::post('item-files/update-images', [ItemFileController::class, 'updateImages'])
        ->name('item-files.update_images');
    Route::resource('item-files', ItemFileController::class);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/items/preview-price', [ItemPricingController::class, 'preview'])
        ->name('fielditems.pricing.preview');
});
