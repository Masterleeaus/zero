# Inventory Finance Material Cost Bridge

## Event Flow
MaterialUsageService → dispatches Inventory\MaterialIssuedToJob(companyId, serviceJobId, itemId, qty, costPerUnit)

## New Finance Events
- `Money\MaterialCostThresholdCrossed` — emitted when material cost exceeds threshold for a job
- `Money\SupplierLiabilityThresholdExceeded` — emitted when total supplier AP liability crosses threshold

## Integration Target
TitanMoney\MaterialCostingService can listen to MaterialIssuedToJob to allocate job costs.
