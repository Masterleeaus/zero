# Inventory Extraction Plan

## Phase 1 — Core Domain (This Pass)

### Step 1: Foundation Models
Order (dependency-safe):
1. `suppliers` table + Supplier model
2. `inventory_items` table + InventoryItem model
3. `warehouses` table + Warehouse model
4. `stock_movements` table + StockMovement model
5. `stocktakes` table + Stocktake model
6. `stocktake_lines` table + StocktakeLine model
7. `purchase_orders` table + PurchaseOrder model
8. `purchase_order_items` table + PurchaseOrderItem model
9. `inventory_audits` table + InventoryAudit model
10. `job_material_usage` table (bridge)
11. Alter `equipment` add `inventory_item_id`
12. Alter `stock_movements` add `purchase_order_id`

### Step 2: Services
1. StockService — onHand(), recordMovement(), adjustStock()
2. SupplierService — createSupplier(), updateSupplier(), getSupplierBalance()
3. PurchaseOrderService — createPurchaseOrder(), receivePurchaseOrder(), calculateTotals(), generatePoNumber()

### Step 3: Routes + Controllers
1. inventory.routes.php
2. InventoryDashboardController
3. SupplierController (CRUD)
4. InventoryItemController (CRUD)
5. WarehouseController (CRUD)
6. PurchaseOrderController (CRUD + receive)
7. StocktakeController (CRUD + finalize)
8. StockMovementController (index)

### Step 4: Views
1. inventory/index.blade.php — dashboard
2. inventory/suppliers/index.blade.php
3. inventory/items/index.blade.php
4. inventory/warehouses/index.blade.php
5. inventory/purchase-orders/index.blade.php
6. inventory/stocktakes/index.blade.php

### Step 5: Tests
1. InventoryDomainTest.php

## Phase 2 — Advanced WMS (Deferred)
- Bin locations (WMSInventoryCore/)
- Reservations and batch tracking
- Vendor payment integration with Money domain
- PO → Bill payables workflow
- Advanced transfer workflows
