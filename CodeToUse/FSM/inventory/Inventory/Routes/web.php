<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\InventoryController;

Route::middleware(['web','auth'])->prefix('inventory')->group(function () {
    Route::get('/', [InventoryController::class, 'index'])->name('inventory.index');
});

// Inventory web UI
Route::middleware(['web','auth'])->prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', function(){ return view('inventory::index'); })->name('home');

    // Items
    Route::get('/items', [\Modules\Inventory\Http\Controllers\ItemWebController::class,'index'])->name('items.index');
    Route::get('/items/create', [\Modules\Inventory\Http\Controllers\ItemWebController::class,'create'])->name('items.create');
    Route::post('/items', [\Modules\Inventory\Http\Controllers\ItemWebController::class,'store'])->name('items.store');
    Route::get('/items/{item}/edit', [\Modules\Inventory\Http\Controllers\ItemWebController::class,'edit'])->name('items.edit');
    Route::put('/items/{item}', [\Modules\Inventory\Http\Controllers\ItemWebController::class,'update'])->name('items.update');
    Route::delete('/items/{item}', [\Modules\Inventory\Http\Controllers\ItemWebController::class,'destroy'])->name('items.destroy');

    // Warehouses
    Route::get('/warehouses', [\Modules\Inventory\Http\Controllers\WarehouseWebController::class,'index'])->name('wh.index');
    Route::get('/warehouses/create', [\Modules\Inventory\Http\Controllers\WarehouseWebController::class,'create'])->name('wh.create');
    Route::post('/warehouses', [\Modules\Inventory\Http\Controllers\WarehouseWebController::class,'store'])->name('wh.store');
    Route::get('/warehouses/{warehouse}/edit', [\Modules\Inventory\Http\Controllers\WarehouseWebController::class,'edit'])->name('wh.edit');
    Route::put('/warehouses/{warehouse}', [\Modules\Inventory\Http\Controllers\WarehouseWebController::class,'update'])->name('wh.update');
    Route::delete('/warehouses/{warehouse}', [\Modules\Inventory\Http\Controllers\WarehouseWebController::class,'destroy'])->name('wh.destroy');

    // Movements
    Route::get('/movements', [\Modules\Inventory\Http\Controllers\MovementWebController::class,'index'])->name('moves.index');
    Route::get('/movements/create', [\Modules\Inventory\Http\Controllers\MovementWebController::class,'create'])->name('moves.create');
    Route::post('/movements', [\Modules\Inventory\Http\Controllers\MovementWebController::class,'store'])->name('moves.store');
});

    // Stocktakes
    Route::get('/stocktakes', [\Modules\Inventory\Http\Controllers\StocktakeWebController::class,'index'])->name('st.index');
    Route::get('/stocktakes/create', [\Modules\Inventory\Http\Controllers\StocktakeWebController::class,'create'])->name('st.create');
    Route::post('/stocktakes', [\Modules\Inventory\Http\Controllers\StocktakeWebController::class,'store'])->name('st.store');
    Route::get('/stocktakes/{stocktake}/edit', [\Modules\Inventory\Http\Controllers\StocktakeWebController::class,'edit'])->name('st.edit');
    Route::post('/stocktakes/{stocktake}/line', [\Modules\Inventory\Http\Controllers\StocktakeWebController::class,'addLine'])->name('st.addLine');
    Route::get('/stocktakes/{stocktake}/export', [\Modules\Inventory\Http\Controllers\StocktakeWebController::class,'exportCsv'])->name('st.export');
    Route::post('/stocktakes/{stocktake}/import', [\Modules\Inventory\Http\Controllers\StocktakeWebController::class,'importCsv'])->name('st.import');
    Route::post('/stocktakes/{stocktake}/finalize', [\Modules\Inventory\Http\Controllers\StocktakeWebController::class,'finalize'])->name('st.finalize');

    // CSV for Items & Warehouses
    Route::get('/items/export', [\Modules\Inventory\Http\Controllers\ItemWebController::class,'exportCsv'])->name('items.export');
    Route::post('/items/import', [\Modules\Inventory\Http\Controllers\ItemWebController::class,'importCsv'])->name('items.import');
    Route::get('/warehouses/export', [\Modules\Inventory\Http\Controllers\WarehouseWebController::class,'exportCsv'])->name('wh.export');
    Route::post('/warehouses/import', [\Modules\Inventory\Http\Controllers\WarehouseWebController::class,'importCsv'])->name('wh.import');

    // Trash & Restore
    Route::get('/items/trashed', [\Modules\Inventory\Http\Controllers\ItemWebController::class,'trashed'])->name('items.trashed');
    Route::post('/items/{id}/restore', [\Modules\Inventory\Http\Controllers\ItemWebController::class,'restore'])->name('items.restore');
    Route::get('/warehouses/trashed', [\Modules\Inventory\Http\Controllers\WarehouseWebController::class,'trashed'])->name('wh.trashed');
    Route::post('/warehouses/{id}/restore', [\Modules\Inventory\Http\Controllers\WarehouseWebController::class,'restore'])->name('wh.restore');

    // Audit log
    Route::get('/audit', [\Modules\Inventory\Http\Controllers\AuditWebController::class,'index'])->name('audit.index');
    Route::get('/audit/{id}', [\Modules\Inventory\Http\Controllers\AuditWebController::class,'show'])->name('audit.show');

    // Movements: trash & bulk
    Route::get('/movements/trashed', [\Modules\Inventory\Http\Controllers\MovementWebController::class,'trashed'])->name('moves.trashed');
    Route::post('/movements/{id}/restore', [\Modules\Inventory\Http\Controllers\MovementWebController::class,'restore'])->name('moves.restore');
    Route::post('/movements/bulk-delete', [\Modules\Inventory\Http\Controllers\MovementWebController::class,'bulkDelete'])->name('moves.bulkDelete');
    Route::post('/movements/bulk-restore', [\Modules\Inventory\Http\Controllers\MovementWebController::class,'bulkRestore'])->name('moves.bulkRestore');

    // Stocktakes: trash & bulk
    Route::get('/stocktakes/trashed', [\Modules\Inventory\Http\Controllers\StocktakeWebController::class,'trashed'])->name('st.trashed');
    Route::post('/stocktakes/{id}/restore', [\Modules\Inventory\Http\Controllers\StocktakeWebController::class,'restore'])->name('st.restore');
    Route::post('/stocktakes/bulk-delete', [\Modules\Inventory\Http\Controllers\StocktakeWebController::class,'bulkDelete'])->name('st.bulkDelete');
    Route::post('/stocktakes/bulk-restore', [\Modules\Inventory\Http\Controllers\StocktakeWebController::class,'bulkRestore'])->name('st.bulkRestore');
