# Inventory Receiving Completion

## Phase 2 Additions to purchase_orders
- `received_by` — FK to user who received
- `received_at` — timestamp of receiving
- `receiving_notes` — optional notes on receipt

## Relation
- `PurchaseOrder::supplierBills()` → HasMany to SupplierBill via purchase_order_id
