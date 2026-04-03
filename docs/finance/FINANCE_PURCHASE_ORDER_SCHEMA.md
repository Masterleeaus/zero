# FINANCE — PURCHASE ORDER SCHEMA

The Finance domain reuses `App\Models\Inventory\PurchaseOrder` (table: `purchase_orders`).

Migration 600200 extends the table with:
- `service_job_id` — nullable job-costing hook

## Fields (all)

| Field           | Notes                                    |
|-----------------|------------------------------------------|
| company_id      | tenancy scope                            |
| po_number       | unique per company                       |
| supplier_id     | FK → suppliers.id                        |
| status          | draft/sent/partial/received/cancelled    |
| order_date      | nullable                                 |
| expected_date   | nullable                                 |
| reference       | nullable                                 |
| currency_code   | default USD                              |
| subtotal        | decimal                                  |
| tax_amount      | decimal                                  |
| total_amount    | decimal                                  |
| service_job_id  | nullable — Phase 7 job costing bridge    |
| notes           | text nullable                            |

## AP Routes

`dashboard.money.purchase-orders.*` via `PurchaseOrderController` (Money namespace).

## Inventory Bridge

`purchase_order_items.item_id` → `inventory_items.id` is already in place via the Inventory domain migration.
