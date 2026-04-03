<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\Api\InventoryApiController;

Route::middleware(['api','auth:sanctum','inventory.tenant'])->prefix('api/inventory')->group(function () {
    Route::get('/items', [InventoryApiController::class, 'items'])->name('inventory.api.items');
    Route::post('/items', [InventoryApiController::class, 'store'])->name('inventory.api.store');
});

// Auto-generated CRUD routes from donor SQL
Route::middleware(['api','auth:sanctum','inventory.tenant'])->prefix('api/inventory')->group(function () {
});

// Stock & Warehouse endpoints
Route::middleware(['api','auth:sanctum','inventory.tenant'])->prefix('api/inventory')->group(function () {
    Route::get('/warehouses', [\Modules\Inventory\Http\Controllers\Api\WarehouseController::class, 'index']);
    Route::post('/warehouses', [\Modules\Inventory\Http\Controllers\Api\WarehouseController::class, 'store']);
    Route::get('/stock/{itemId}/on-hand', [\Modules\Inventory\Http\Controllers\Api\StockController::class, 'onHand']);
    Route::post('/stock/move', [\Modules\Inventory\Http\Controllers\Api\StockController::class, 'move']);
});

// Movements + Stocktakes API
Route::middleware(['api','auth:sanctum','inventory.tenant'])->prefix('api/inventory')->group(function () {
    Route::get('/movements', [\Modules\Inventory\Http\Controllers\Api\MovementsApiController::class, 'index']);
    Route::post('/movements', [\Modules\Inventory\Http\Controllers\Api\MovementsApiController::class, 'store']);
    Route::get('/stocktakes', [\Modules\Inventory\Http\Controllers\Api\StocktakesApiController::class, 'index']);
    Route::get('/stocktakes/{id}', [\Modules\Inventory\Http\Controllers\Api\StocktakesApiController::class, 'show']);
    Route::post('/stocktakes', [\Modules\Inventory\Http\Controllers\Api\StocktakesApiController::class, 'store']);
    Route::post('/stocktakes/{id}/finalize', [\Modules\Inventory\Http\Controllers\Api\StocktakesApiController::class, 'finalize']);
});
