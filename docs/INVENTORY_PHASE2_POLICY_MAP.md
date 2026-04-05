# Inventory Phase 2 Policy Map

## Authentication
All new routes are behind `auth` + `updateUserActivity` middleware.

## Company Scoping
- ReorderRecommendationService: filters by `company_id`
- ReorderSignalService: filters by `company_id`
- MaterialUsageService: writes `company_id` to all new records
- APBridgeController: uses PO's `company_id` (route model binding enforces scope via BelongsToCompany global scope)

## Idempotency
- Stocktake finalize: guard on status === 'final'
- SupplierBillService::createFromPurchaseOrder: returns existing non-cancelled bill
