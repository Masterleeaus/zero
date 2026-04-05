# Inventory Finance AP Bridge

## APBridgeController
Route: POST /inventory/purchase-orders/{purchaseOrder}/create-bill

## SupplierBillService::createFromPurchaseOrder()
- Idempotent: returns existing non-cancelled bill for PO
- Creates SupplierBill (draft) linked to PO
- Creates SupplierBillItem rows per PO line (uses qty_received if > 0, else qty_ordered)
- Returns bill with items loaded

## Data Flow
PurchaseOrder → SupplierBill (draft) → SupplierBillItems
