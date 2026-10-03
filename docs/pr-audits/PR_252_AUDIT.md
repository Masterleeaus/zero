# PR #252 — Inventory Phase 2 + Finance Pass 5A Completion

**Status:** HOLD — WIP DRAFT (no implementation committed)  
**Risk Level:** High  
**Domain:** Inventory / Finance / TitanMoney  
**Audit Date:** 2026-04-05  
**Auditor:** GitHub Copilot Merge/Review Agent  

---

## 1. Purpose

Extends the Inventory Phase 1 and Finance Phase 1–4 foundations with:

- Receiving completion (partial/full PO receiving with audit trail)
- Stocktake finalization integrity (variance calculation, adjustment creation)
- Internal transfer + issue-to-job movement flow
- Material consumption → job costing bridge
- Purchase → Supplier Bill / AP bridge
- Low-stock thresholds + reorder model (reorder_qty, min_stock, preferred_supplier_id)
- Procurement and finance signal hooks (low stock, variance, material cost anomalies)
- Reorder recommendation engine (suggestion-only, not autonomous)
- Route/UI surfaces for new operational flows
- Policies, tenancy, audit gates
- Test coverage for all new layers
- Documentation updates across inventory and finance domains

This is described as a "bridge pass" — finishing key missing inventory operational logic and
delivering the first half of Finance Pass 5 as a signal/recommendation layer.

---

## 2. Scope

13 stages across:

| Stage | Area |
|---|---|
| 1 | Current state recheck — full repo inventory/finance scan |
| 2 | Receiving completion layer |
| 3 | Stocktake + adjustment integrity |
| 4 | Internal transfer / issue-to-job flow |
| 5 | Material consumption → job costing bridge |
| 6 | Purchase → Supplier Bill / AP bridge |
| 7 | Low-stock thresholds + reorder model |
| 8 | Signals / automation (Finance Pass 5A) |
| 9 | Decision / reorder recommendation engine |
| 10 | Routes / UI / reporting surfaces |
| 11 | Policies / tenancy / audit |
| 12 | Test coverage |
| 13 | Documentation updates |

**Main folders/files to be touched:**
- `app/Models/Inventory/` — extend InventoryItem, Stocktake, StockMovement
- `app/Services/Inventory/` — extend StockService, PurchaseOrderService, add MaterialUsageService, ReorderSignalService, ReorderRecommendationService
- `app/Services/TitanMoney/` — extend MaterialCostingService, FinancialSignalService
- `database/migrations/` — new migration(s) for reorder columns, receiving fields
- `routes/core/` — extend inventory.routes.php, money.routes.php
- `resources/views/` — new Blade views for receiving, stocktake, reorder surfaces
- `app/Policies/` — new or extended inventory policies
- `app/Events/Inventory/` — new signal events
- `tests/Feature/` — new inventory/finance tests
- `docs/` — 13+ new or updated docs

---

## 3. Structural Fit

### What already exists (confirmed by scan):

| Component | State |
|---|---|
| `InventoryItem` model | ✅ Present — `reorder_point` exists, missing `reorder_qty`, `min_stock`, `preferred_supplier_id`, `low_stock` flag |
| `StockMovement` model | ✅ Present — types: `in/out/adjust/transfer` in migration comment; `moveable` polymorphic support present; missing `issue`/`return` type handling in service |
| `Stocktake` model | ✅ Present — has `status` (draft/in_progress/final); missing: finalized_by, finalized_at, variance tracking |
| `StocktakeLine` model | ✅ Present |
| `PurchaseOrder` model | ✅ Present |
| `PurchaseOrderItem` model | ✅ Present — has `qty_received` |
| `PurchaseOrderService` | ✅ Present — `receivePurchaseOrder()` exists for partial/full receipt; missing: warehouse destination enforcement, idempotency guard, over-receipt protection |
| `StockService` | ✅ Present — `onHand()`, `recordMovement()`, `adjustStock()` |
| `MaterialCostingService` | ✅ Present in `app/Services/TitanMoney/` |
| `FinancialSignalService` | ✅ Present in `app/Services/TitanMoney/` |
| `SupplierBillService` | ✅ Present in `app/Services/TitanMoney/` |
| `AccountingService` | ✅ Present in `app/Services/TitanMoney/` |

### Gaps confirmed by scan:

| Gap | Impact |
|---|---|
| `inventory_items` missing `reorder_qty`, `min_stock`, `preferred_supplier_id` columns | Medium — needs migration |
| `InventoryItem` model missing `low_stock` computed accessor | Low |
| `StockService` missing `issueToJob()`, `transferStock()` | Medium |
| `Stocktake` missing finalize service logic + variant creation | High |
| No `MaterialUsageService` (separate from MaterialCostingService) | Medium |
| No `ReorderSignalService` | Medium |
| No `ReorderRecommendationService` | Medium |
| No inventory-specific events (`InventoryLowStockDetected`, `StockVarianceDetected`, `MaterialCostThresholdCrossed`) | Medium |
| No PO → SupplierBill draft bridge in PurchaseOrderService | Medium |
| Receiving flow lacks: warehouse destination enforcement, receiving_notes, received_by, idempotency guard | Medium |
| No inventory routes for stocktake finalize, issue-to-job, reorder surfaces | Medium |
| No policies for stocktake finalize, issue-to-job, reorder visibility | Low–Medium |

### Architecture alignment:

✅ Inventory domain is cleanly isolated at `App\Models\Inventory\`  
✅ TitanMoney services already exist — Material + Signal services can be extended  
✅ `company_id` tenancy enforced via `BelongsToCompany` concern  
✅ Route naming convention: `inventory.*` (confirmed in existing routes)  
✅ Migration idempotency pattern (`Schema::hasTable`) established in `700100`  
✅ No architectural conflicts with existing FSM, HRM, Mesh, or Finance modules

---

## 4. Code Quality

**Current commit state:** 1 commit — "Initial plan" — contains **zero file changes**.

The PR head branch `copilot/inventory-phase-2-finance-pass-5a` diverges from main by exactly
one empty plan commit (SHA `c1d0ca30`). No models, services, migrations, routes, views, tests,
or documentation have been written.

This is expected for a WIP draft PR. The code quality of the eventual implementation will need
to be assessed in a follow-up audit pass.

**Implementation risks to watch (when coding begins):**

| Risk | Mitigation |
|---|---|
| Duplicate supplier bill logic (Finance Pass 2 already added SupplierBillService) | Extend, do not replace |
| Duplicate material costing logic (MaterialCostingService exists) | Connect via observer/event, do not rebuild |
| Migration collision with existing `700100` inventory tables | Use additive migration, not ALTER of existing |
| InventoryItem `reorder_point` already exists — `reorder_qty` addition must be additive | Additive migration only |
| `FinancialSignalService` already exists — inventory signals should extend it | Add methods, do not recreate |
| `transferStock()` collision with existing `transfer` type in StockMovement | Reuse existing type, add movement sub-type if needed |

---

## 5. Conflict Review

**Git conflicts:** None — the PR head is 1 commit ahead of main with no file changes.

**Semantic conflicts anticipated:**

| Conflict Area | Risk |
|---|---|
| `SupplierBillService` — existing class must be extended, not replaced | Medium |
| `MaterialCostingService` — existing class; need to connect to inventory issue events | Low |
| `FinancialSignalService` — existing class; extend with inventory signal methods | Low |
| `EventServiceProvider::$listen` — new inventory events must be appended cleanly | Low |
| `inventory_items` migration — additive columns only; `700100` must not be modified | Medium |

---

## 6. Merge Decision

**HOLD — WIP DRAFT**

**Reason:**
- The PR contains no file changes whatsoever — only an empty "Initial plan" commit
- The PR is marked as a GitHub Draft
- No implementation has been started
- All 13 stages remain unimplemented
- The scope is well-defined and the existing foundation is ready to receive the work

**Conditions for merge:**
1. All 13 stages implemented and committed to the PR branch
2. Migration(s) created using additive-only pattern, no timestamp collisions
3. EventServiceProvider updated with new inventory events
4. Tests passing for: partial/full receiving, stocktake finalize, issue-to-job, low-stock detection, reorder recommendations, PO→ bill bridge, signal emission
5. No duplication of SupplierBillService, MaterialCostingService, or FinancialSignalService
6. All 13 documentation files created
7. PR removed from Draft status

---

## 7. Gap Analysis

### Missing implementation (full PR scope):

| Category | Missing |
|---|---|
| **Receiving** | Warehouse destination enforcement, idempotency guard, over-receipt protection, receiving_notes/received_by fields |
| **Stocktake** | `StocktakeService::finalize()`, finalized_at/finalized_by columns, variance movement creation |
| **Stock movements** | `issue` and `return` types fully wired in StockService, `issueToJob()` method |
| **Material usage** | `MaterialUsageService`, usage→costing linkage |
| **AP bridge** | `PurchaseOrderService::generateSupplierBillDraft()` or equivalent |
| **Reorder model** | `reorder_qty`, `min_stock`, `preferred_supplier_id` columns on `inventory_items` |
| **Signals** | `ReorderSignalService`, `InventoryLowStockDetected`, `SupplierLiabilityThresholdExceeded`, `MaterialCostThresholdCrossed`, `StockVarianceDetected` events |
| **Recommendations** | `ReorderRecommendationService` |
| **Routes** | `inventory.stocktakes.finalize`, `inventory.movements.issue`, `inventory.reorder.index` |
| **Policies** | Stocktake finalize, issue-to-job, reorder visibility, AP bridge |
| **Tests** | All new services and flows |
| **Docs** | 13 new/updated documentation files |

### Follow-up work required:

- **Next pass:** Full implementation of PR #252 scope
- **Priority:** Medium-High (inventory operational completeness blocks real-world usage)
- **Predecessor passes satisfied:** Finance Pass 1–4 ✅, HRM Pass 1–2 ✅, Inventory Phase 1 ✅

---

## 8. Integration Notes

When this PR is implemented, it should connect to:

| System | Integration point |
|---|---|
| `MaterialCostingService` | Extend with `postFromStockIssue(StockMovement $movement)` |
| `FinancialSignalService` | Add `checkInventorySignals(InventoryItem $item)` etc. |
| `SupplierBillService` | Add `createFromPurchaseOrder(PurchaseOrder $po)` |
| `AccountingService` | AP liability posting on supplier bill creation |
| `EventServiceProvider` | Append inventory signal events to `$listen` array |
| `JobCostAllocation` | Link material usage movements to job cost allocations |
