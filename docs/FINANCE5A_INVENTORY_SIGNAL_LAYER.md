# Finance Pass 5A — Inventory Signal Layer

## New Events

### Inventory Namespace
- `Inventory\InventoryLowStockDetected` — (companyId, detail[])
- `Inventory\StockVarianceDetected` — (companyId, detail[])
- `Inventory\ReorderSuggested` — (companyId, recommendations[])
- `Inventory\MaterialIssuedToJob` — (companyId, serviceJobId, itemId, qty, costPerUnit)

### Money Namespace
- `Money\SupplierLiabilityThresholdExceeded` — (companyId, detail[])
- `Money\MaterialCostThresholdCrossed` — (companyId, serviceJobId, detail[])

## Registration
All events registered in EventServiceProvider with empty listener arrays (ready for future listeners).
