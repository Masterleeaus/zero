# Inventory Connection Map

## Domain Connections

### InventoryItem → ServiceJob (via job_material_usage)
- Table: `job_material_usage`
- Columns: job_id (FK → service_jobs), item_id (FK → inventory_items), warehouse_id, qty_used, cost_per_unit, note
- Purpose: Track parts/materials consumed during field service jobs
- Trigger: Technician marks items used on job completion

### InventoryItem → Equipment (via equipment.inventory_item_id)
- Column: `equipment.inventory_item_id` (nullable FK → inventory_items)
- Purpose: Link physical installed equipment back to inventory catalogue item
- Trigger: Equipment procurement from PO, or stock issue to site

### PurchaseOrder → Supplier
- Column: `purchase_orders.supplier_id` (FK → suppliers)
- Purpose: PO belongs to a supplier

### StockMovement → PurchaseOrder (via stock_movements.purchase_order_id)
- Column: `stock_movements.purchase_order_id` (nullable FK → purchase_orders)
- Purpose: When PO is received, stock movements reference the PO

### StockMovement → ServiceJob (via morph: moveable_type/moveable_id)
- Polymorphic: moveable_type = 'App\Models\Work\ServiceJob'
- Purpose: Stock out movements reference the job that consumed materials

### Supplier → Customer (structural similarity)
- Supplier mirrors Customer structure (not FK — separate entities)
- Both use BelongsToCompany + OwnedByUser
- Future: unified contact entity could bridge both

## Service Integrations

| Service | Connects To | Method |
|---------|-------------|--------|
| StockService | InventoryItem, StockMovement, Warehouse | Direct model ops |
| PurchaseOrderService | PurchaseOrder, Supplier, StockService | Service composition |
| SupplierService | Supplier, PurchaseOrder | Direct model ops |

## Route Namespaces
- Prefix: `dashboard.inventory.*`
- All routes under: `routes/core/inventory.routes.php`
- Controllers: `App\Http\Controllers\Core\Inventory\`
