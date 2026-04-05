<?php

use App\Http\Controllers\Core\Inventory\APBridgeController;
use App\Http\Controllers\Core\Inventory\InventoryDashboardController;
use App\Http\Controllers\Core\Inventory\InventoryItemController;
use App\Http\Controllers\Core\Inventory\PurchaseOrderController;
use App\Http\Controllers\Core\Inventory\ReorderController;
use App\Http\Controllers\Core\Inventory\StockIssueController;
use App\Http\Controllers\Core\Inventory\StockMovementController;
use App\Http\Controllers\Core\Inventory\StocktakeController;
use App\Http\Controllers\Core\Inventory\SupplierController;
use App\Http\Controllers\Core\Inventory\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity', 'throttle:' . config('throttle.dashboard', '120,1')])
    ->prefix('dashboard')
    ->as('dashboard.')
    ->group(static function () {
        Route::prefix('inventory')->as('inventory.')->group(static function () {
            Route::get('', [InventoryDashboardController::class, 'index'])->name('index');

            // Suppliers
            Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
            Route::get('suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
            Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
            Route::get('suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
            Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
            Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
            Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

            // Inventory Items
            Route::get('items', [InventoryItemController::class, 'index'])->name('items.index');
            Route::get('items/create', [InventoryItemController::class, 'create'])->name('items.create');
            Route::post('items', [InventoryItemController::class, 'store'])->name('items.store');
            Route::get('items/{item}', [InventoryItemController::class, 'show'])->name('items.show');
            Route::get('items/{item}/edit', [InventoryItemController::class, 'edit'])->name('items.edit');
            Route::put('items/{item}', [InventoryItemController::class, 'update'])->name('items.update');
            Route::delete('items/{item}', [InventoryItemController::class, 'destroy'])->name('items.destroy');

            // Warehouses
            Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
            Route::get('warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
            Route::post('warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
            Route::get('warehouses/{warehouse}', [WarehouseController::class, 'show'])->name('warehouses.show');
            Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
            Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
            Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');

            // Purchase Orders
            Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
            Route::get('purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
            Route::post('purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
            Route::get('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
            Route::get('purchase-orders/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit');
            Route::put('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('purchase-orders.update');
            Route::delete('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy');
            Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');

            // AP Bridge: generate supplier bill from PO
            Route::post('purchase-orders/{purchaseOrder}/create-bill', [APBridgeController::class, 'createBillFromPO'])->name('purchase-orders.create-bill');

            // Stocktakes
            Route::get('stocktakes', [StocktakeController::class, 'index'])->name('stocktakes.index');
            Route::get('stocktakes/create', [StocktakeController::class, 'create'])->name('stocktakes.create');
            Route::post('stocktakes', [StocktakeController::class, 'store'])->name('stocktakes.store');
            Route::get('stocktakes/{stocktake}', [StocktakeController::class, 'show'])->name('stocktakes.show');
            Route::get('stocktakes/{stocktake}/edit', [StocktakeController::class, 'edit'])->name('stocktakes.edit');
            Route::put('stocktakes/{stocktake}', [StocktakeController::class, 'update'])->name('stocktakes.update');
            Route::delete('stocktakes/{stocktake}', [StocktakeController::class, 'destroy'])->name('stocktakes.destroy');
            Route::post('stocktakes/{stocktake}/finalize', [StocktakeController::class, 'finalize'])->name('stocktakes.finalize');

            // Stock Movements (read-only)
            Route::get('stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index');

            // Stock Issue to Job
            Route::get('stock-issue/create', [StockIssueController::class, 'create'])->name('stock-issue.create');
            Route::post('stock-issue', [StockIssueController::class, 'store'])->name('stock-issue.store');

            // Reorder recommendations
            Route::get('reorder', [ReorderController::class, 'index'])->name('reorder.index');
            Route::post('reorder/scan', [ReorderController::class, 'scan'])->name('reorder.scan');

            // Audit log
            Route::get('audit', [InventoryDashboardController::class, 'audit'])->name('audit');
        });
    });
