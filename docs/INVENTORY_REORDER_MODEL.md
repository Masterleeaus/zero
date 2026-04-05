# Inventory Reorder Model

## New inventory_items Columns
| Column | Type | Description |
|---|---|---|
| reorder_qty | integer | Quantity to order when reorder triggered |
| min_stock | integer | Absolute minimum stock before critical alert |
| preferred_supplier_id | unsignedBigInteger nullable | FK to suppliers |
| low_stock_flag | boolean | Set true by ReorderSignalService when qty_on_hand <= reorder_point |

## Logic
- `isLowStock()` method: returns `qty_on_hand <= reorder_point`
- `low_stock_flag` is set/cleared by `ReorderSignalService::detectLowStock()`
