# Inventory Reorder Recommendations

## Service: ReorderRecommendationService
- Queries items where `qty_on_hand <= reorder_point` for company
- Checks open PO qty to avoid duplicate orders
- Calculates `suggested_order_qty = max(reorder_qty, shortfall)`
- Returns sorted array: critical > high > medium

## Priority Logic
- critical: qty_on_hand <= 0
- high: qty_on_hand <= min_stock
- medium: below reorder_point

## Route
GET /inventory/reorder → ReorderController@index
POST /inventory/reorder/scan → ReorderController@scan (emits low-stock signals)
