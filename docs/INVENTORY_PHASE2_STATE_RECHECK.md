# Inventory Phase 2 — State Recheck

**Date:** 2026-04-05  
**Pass:** Inventory Phase 2 + Finance Pass 5A

## Models Extended
- `InventoryItem`: +reorder_qty, +min_stock, +preferred_supplier_id, +low_stock_flag, +isLowStock(), +preferredSupplier()
- `Stocktake`: +finalized_by, +finalized_at, +adjustment_reason
- `StocktakeLine`: +variance, +note
- `PurchaseOrder`: +received_by, +received_at, +receiving_notes, +supplierBills()
- `StockMovement`: +service_job_id, +movement_reason

## Services Added
- `MaterialUsageService` — issue to job, warehouse transfer
- `ReorderSignalService` — low-stock detection, variance detection
- `ReorderRecommendationService` — reorder recommendation generation

## Finance Bridge
- `SupplierBillService::createFromPurchaseOrder()` — idempotent AP bill generation from PO
