# FINANCE — PURCHASE → INVENTORY BRIDGE

## Current State

`purchase_order_items.item_id` is a nullable FK to `inventory_items.id`.

This is already present from Inventory Domain Phase 1 (migration 700100).

## Finance AP Usage

When a supplier bill is created from a purchase order:

```
PurchaseOrder → SupplierBill
   purchase_order_id = po.id
```

Line items on the bill currently use `account_id` (expense account) rather than `item_id`.

## Hook Points

- `purchase_orders.service_job_id` → job costing
- `purchase_order_items.item_id` → inventory item (receive stock)
- `supplier_bill_lines.account_id` → expense account

## Inventory Receive Flow (future)

When PO status changes to `received`:
1. `PurchaseOrderService::receive()` updates stock movements
2. Inventory quantities are adjusted
3. The corresponding `SupplierBill` can be created from the PO

This bridge is already partially in place via `Inventory\PurchaseOrderService`.
