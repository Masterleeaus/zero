# MODEL_STABILISATION_REPORT.md

**Phase 9 — Step 5: Model Stabilisation**
**Date:** 2026-04-04
**Pass:** Phase 9 — Critical Structural Stabilisation

---

## Collisions Resolved

### 1. `SiteAsset` — CRITICAL (Resolved)

**Canonical model:** `App\Models\Facility\SiteAsset`
**Duplicate model:** `App\Models\Work\SiteAsset`
**Table:** `site_assets`

Both models targeted the same `site_assets` table with slightly different relationship sets.

#### Fix applied

`app/Models/Equipment/EquipmentWarranty.php` was the only active host file importing `App\Models\Work\SiteAsset`. Updated to use `App\Models\Facility\SiteAsset`.

`app/Models/Work/SiteAsset.php` has been annotated with a `@deprecated` docblock pointing to the canonical `App\Models\Facility\SiteAsset`. It is not deleted — it may be referenced from CodeToUse source bundles or tests — but it should not be used in any new active code.

**Active references to canonical model (App\Models\Facility\SiteAsset):**
- `app/Models/Meter/Meter.php`
- `app/Models/Premises/Unit.php`
- `app/Models/Premises/Building.php`
- `app/Models/Premises/Premises.php`
- `app/Models/Repair/RepairOrder.php`
- `app/Models/Crm/Customer.php`
- `app/Listeners/Predict/UpdateAssetPredictionOnServiceEvent.php`
- `app/Listeners/Work/ServicePlanVisitCompletedListener.php`
- `app/Http/Controllers/Predict/TitanPredictController.php`
- `app/Services/Predict/PredictionSignalExtractorService.php`
- `app/Services/Predict/TitanPredictService.php`
- `app/Services/Scheduling/CustomerTimelineAggregator.php`
- `app/Models/Equipment/EquipmentWarranty.php` ← **fixed in this pass**

**Files changed:**
- `app/Models/Equipment/EquipmentWarranty.php`
- `app/Models/Work/SiteAsset.php` (annotated `@deprecated`)

---

### 2. `InspectionInstance` — CRITICAL (Resolved)

**Canonical model:** `App\Models\Inspection\InspectionInstance`
**Duplicate model:** `App\Models\Work\InspectionInstance`
**Table:** `inspection_instances`

The canonical `App\Models\Inspection\InspectionInstance` implements `SchedulableEntity` and is integrated with the scheduling surface. The `App\Models\Work\InspectionInstance` version does not implement this interface.

#### Fix applied

Two active host files were importing `App\Models\Work\InspectionInstance`:

1. `app/Models/Premises/Premises.php` — updated `use` import to `App\Models\Inspection\InspectionInstance`
2. `app/Events/Work/InspectionCompleted.php` — updated `use` import to `App\Models\Inspection\InspectionInstance`

`app/Models/Work/InspectionInstance.php` has been annotated with a `@deprecated` docblock pointing to the canonical `App\Models\Inspection\InspectionInstance`.

**Files changed:**
- `app/Models/Premises/Premises.php`
- `app/Events/Work/InspectionCompleted.php`
- `app/Models/Work/InspectionInstance.php` (annotated `@deprecated`)

---

## Active Model Namespace Overview (Post-Fix)

| Table | Canonical Model | Deprecated Duplicate |
|-------|----------------|---------------------|
| `site_assets` | `App\Models\Facility\SiteAsset` | `App\Models\Work\SiteAsset` (annotated) |
| `inspection_instances` | `App\Models\Inspection\InspectionInstance` | `App\Models\Work\InspectionInstance` (annotated) |

---

## Remaining Risks

| Risk | Status |
|------|--------|
| Deprecated duplicate models still physically exist | By design — not deleted to preserve CodeToUse source compatibility. Future cleanup pass may remove them. |
| `App\Models\Finance\` vs `App\Models\Money\` naming confusion | Not a collision — different domains (SaaS subscriptions vs business finance). No fix needed. |
| Tenant scope verification for SiteAsset | Both `Facility\SiteAsset` and `Work\SiteAsset` use `BelongsToCompany` trait. No scope gap identified. |
