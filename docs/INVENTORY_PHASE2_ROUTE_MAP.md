# Inventory Phase 2 Route Map

All routes under prefix: `/dashboard/inventory/` with name prefix: `dashboard.inventory.`

| Method | URI | Name | Controller |
|---|---|---|---|
| GET | stock-issue/create | stock-issue.create | StockIssueController@create |
| POST | stock-issue | stock-issue.store | StockIssueController@store |
| GET | reorder | reorder.index | ReorderController@index |
| POST | reorder/scan | reorder.scan | ReorderController@scan |
| POST | purchase-orders/{po}/create-bill | purchase-orders.create-bill | APBridgeController@createBillFromPO |
