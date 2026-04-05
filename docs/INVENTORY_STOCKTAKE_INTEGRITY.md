# Inventory Stocktake Integrity

## Finalization Process
1. Idempotency guard: abort if status=final
2. For each line: calculate variance (counted - current_on_hand)
3. Update line with expected_qty and variance
4. Record adjust movement if variance != 0
5. Set finalized_by, finalized_at, adjustment_reason on stocktake
6. Emit StockVarianceDetected events via ReorderSignalService

## New Columns
- `stocktakes.finalized_by` — user ID
- `stocktakes.finalized_at` — datetime
- `stocktakes.adjustment_reason` — string
- `stocktake_lines.variance` — integer (counted - expected)
- `stocktake_lines.note` — text
