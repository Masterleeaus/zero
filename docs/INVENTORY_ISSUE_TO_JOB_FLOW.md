# Inventory Issue-to-Job Flow

## Controller
`StockIssueController` — GET /inventory/stock-issue/create, POST /inventory/stock-issue

## Service
`MaterialUsageService::issueToJob()`:
1. Find item, resolve cost_per_unit (fallback to item.cost_price)
2. Record StockMovement type='issue', qty_change=-qty
3. Insert row into job_material_usage
4. Dispatch Inventory\MaterialIssuedToJob event

## DB Impact
- stock_movements: new 'issue' record with service_job_id
- job_material_usage: new row with stock_movement_id link
- inventory_items.qty_on_hand: decremented

## stock_movements New Columns
- `service_job_id` — indexed FK to service job
- `movement_reason` — e.g. issue_to_job, stocktake, transfer
