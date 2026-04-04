# PHASE9_STABILISATION_REPORT.md

**Phase 9 — Final Report**
**Date:** 2026-04-04
**Pass:** Critical Structural Stabilisation

---

## Objective

Make the host repository safer and cleaner by fixing critical structural risks that can break:
- fresh installs
- autoload resolution
- route loading
- provider boot
- model resolution
- future integration passes

No feature expansion was performed in this pass.

---

## What Was Quarantined

12 source trees moved to `CodeToUse/_Quarantine/`. Full manifest in `docs/QUARANTINE_MANIFEST.md`.

| Tree | Reason |
|------|--------|
| `CodeToUse/AI/aicore/AICores/` | Exact duplicate of `CodeToUse/AI/AICores/` |
| `CodeToUse/AI/aicore/titancore/` | Shadow copy of the host repository — maximum confusion risk |
| `CodeToUse/AI/AiSocialMedia/` | Stale v4.5.0 superseded by `Comms/SocialMedia` v5.1.0 |
| `CodeToUse/Voice/TitanVoiceSuite_Pass1/` | Legacy — superseded |
| `CodeToUse/Voice/TitanVoiceSuite_Pass2/` | Legacy — superseded |
| `CodeToUse/Voice/TitanVoiceSuite_Pass3/` | Legacy — superseded |
| `CodeToUse/Voice/TitanVoiceSuite_Pass8_Full/` | Partial upgrade — superseded |
| `CodeToUse/Voice/TitanVoiceSuite_Pass11_Full/` | Partial upgrade — superseded |
| `CodeToUse/Voice/TitanVoiceSuite_FULL_Real_Recreated/` | Superseded by Unified and Pass26 |
| `CodeToUse/Voice/MagicAI_TitanVoice_True_Minimal_Overlay_v2/` | Superseded by Pass26 HARDENED |
| `CodeToUse/Voice/TitanOmni_TotalCodebase_Pass24_SystemOnly/` | Superseded by Pass26 |
| `CodeToUse/Voice/TitanOmni_SystemOnly_Pass26/` | Superseded by `TitanOmni Complete Pass26 HARDENED` |

**Content preserved:** All quarantined trees retained in `CodeToUse/_Quarantine/`.

---

## Active Criticals Fixed

### 1. Autoload Collision — FIXED

**File:** `composer.json`

Removed `"App\\Extensions\\": "CodeToUse/"` PSR-4 mapping. This mapping caused Composer to treat `CodeToUse/` as an active PHP class source competing with `app/Extensions/`. With 2,745+ `App\Models` files in CodeToUse and hundreds of `App\Http\Controllers` files, any `composer dump-autoload` could produce unpredictable class resolution.

`CodeToUse/` is now correctly treated as an archived source staging area with no autoload participation.

### 2. Migration Collision (`tz_signals`) — FIXED

**File:** `database/migrations/2026_03_31_000100_add_federation_metadata_and_tables.php`

Added `if (! Schema::hasTable('tz_signals'))` guard. The `tz_signals` table is created by `2026_03_30_220000_create_titan_signal_tables.php` which runs first. The federation migration was attempting to create it again without a guard, breaking fresh installs.

### 3. Migration Collision (`tz_rewind_snapshots`, `tz_rewind_snapshot_items`, `tz_rewind_restores`) — FIXED

**File:** `database/migrations/2026_03_31_000100_add_federation_metadata_and_tables.php`

Added `hasTable` guards for all three rewind-related creates in the federation migration. The dedicated `2026_03_31_100007_create_tz_rewind_snapshots_table.php` is the canonical owner of `tz_rewind_snapshots`.

### 4. Model Collision (`SiteAsset`) — FIXED

- `app/Models/Equipment/EquipmentWarranty.php` updated to use `App\Models\Facility\SiteAsset` (canonical)
- `app/Models/Work/SiteAsset.php` annotated `@deprecated`

### 5. Model Collision (`InspectionInstance`) — FIXED

- `app/Models/Premises/Premises.php` updated to import `App\Models\Inspection\InspectionInstance` (canonical)
- `app/Events/Work/InspectionCompleted.php` updated to import `App\Models\Inspection\InspectionInstance` (canonical)
- `app/Models/Work/InspectionInstance.php` annotated `@deprecated`

### 6. Route Loader Boundary — VERIFIED SAFE

CodeToUse route files cannot be accidentally loaded. `loadCoreRoutes()` globs only `routes/core/*.routes.php`. No action required.

### 7. Provider Boot Surface — VERIFIED CLEAN

No duplicate providers registered in `config/app.php`. `TitanRewindServiceProvider` path ambiguity resolved by autoload fix. CodeToUse providers cannot be instantiated without explicit import.

### 8. Mobile Canonical Path — DOCUMENTED

`mobile_apps/` designated canonical. `CodeToUse/Mobile/` designated reference-only. See `docs/MOBILE_CANONICAL_PATH_DECISION.md`.

---

## What Remains Blocked / Deferred

| Item | Status | Reason |
|------|--------|--------|
| `tz_rewind_snapshots` schema unification (federation vs TitanRewind) | **Needs architectural decision** | Two incompatible schemas targeting the same table; cannot unify without choosing one |
| `App\Models\Work\SiteAsset` removal | **Deferred safely** | Deprecated and annotated; safe to remove in a future cleanup pass after confirming no CodeToUse references need it |
| `App\Models\Work\InspectionInstance` removal | **Deferred safely** | Same as above |
| Voice integration (choose canonical) | **Deferred** | Two canonical candidates retained: `TitanVoiceSuite_Unified_Merged_From_Largest_Base` and `TitanOmni Complete Pass26 HARDENED`. Integration pass required. |
| CRM `leads` duplicate (`Lead/` vs `leads/`) | **Deferred** | Source-only, not affecting active host |
| `CodeToUse/Signals/` archival | **Deferred** | Content appears already integrated; explicit archival deferred |
| Mobile `baseUrl` configuration | **Deferred** | Pre-build configuration task |

---

## What Is Now Safe

| Item | Safety Status |
|------|--------------|
| Fresh install migration sequence | ✅ Safe — critical `tz_signals` and `tz_rewind_snapshots` collisions resolved |
| Composer autoload | ✅ Safe — `CodeToUse/` no longer participates in PSR-4 resolution |
| Route loader | ✅ Safe — CodeToUse routes are not reachable via glob loader |
| Provider boot | ✅ Safe — no duplicate registrations; autoload fix prevents accidental CodeToUse provider activation |
| Model resolution for `site_assets` | ✅ Safe — single canonical model actively used |
| Model resolution for `inspection_instances` | ✅ Safe — single canonical model actively used |
| Mobile development path | ✅ Documented — `mobile_apps/` is canonical |

---

## Recommended Next Pass

Following Phase 9 completion, the recommended order for subsequent passes:

### Pass 1 — Route/Controller Repair
- Verify all routes in `routes/core/*.routes.php` have corresponding controller files
- Resolve any `CONTROLLER_NAMESPACE_DRIFT.md` issues
- Verify named route consistency

### Pass 2 — Middleware/Auth Drift Repair
- Audit middleware chains on all route groups
- Verify auth middleware is consistently applied
- Resolve any gaps identified in `MIDDLEWARE_ROUTE_AUDIT.md`

### Pass 3 — Controlled Extension Intake
- Choose one canonical Voice bundle (`TitanVoiceSuite_Unified` or `TitanOmni Pass26 HARDENED`)
- Plan controlled intake of Comms/SocialMedia (v5.1.0)
- Evaluate CRM leads module (resolve Lead vs leads duplicate)

### Pass 4 — Domain-by-Domain Integration
- Finance completion
- HRM domain stabilisation
- Inventory domain stabilisation
- Security domain integration

---

## Success Criteria Assessment

| Criterion | Met? |
|-----------|------|
| Extracted duplicate trees are quarantined from active runtime | ✅ Yes |
| Active autoload conflicts removed or neutralised | ✅ Yes |
| Fresh-install-breaking migration collisions resolved | ✅ Yes |
| Active route loaders no longer risk loading extracted source trees | ✅ Yes |
| Active model collisions reduced to one canonical runtime path | ✅ Yes |
| Provider boot surface cleaner and safer | ✅ Yes |
| One canonical mobile source path documented | ✅ Yes |
| No feature expansion occurred | ✅ Yes |

**Phase 9 pass status: COMPLETE**
