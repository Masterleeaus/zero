# PR #240 — Phase 9: Critical Structural Stabilisation

**Status:** MERGED WITH MINOR ADJUSTMENT (Audit Pass 2 — 2026-04-04)  
**Risk Level:** Medium (structural impact; no feature additions)  
**Domain:** Infrastructure / Autoload / Migrations / Models

## 1. Purpose

Controlled repair pass targeting structural risks that would break fresh installs, autoload
resolution, and future integration. No features added. All changes are stability improvements.

## 2. Scope

| Area | Changes |
|---|---|
| `composer.json` | Removed `App\\Extensions\\` → `CodeToUse/` PSR-4 mapping |
| Federation migration | Added `Schema::hasTable()` guards for `tz_signals` and `tz_rewind_snapshots` |
| Model deduplication | `InspectionCompleted`, `EquipmentWarranty`, `Premises`, `InspectionInstance`, `SiteAsset` |
| Source quarantine | 9 Voice stale copies + AiSocialMedia v4.5 + aicore shadow copies moved to `CodeToUse/_Quarantine/` |
| Docs | 8 stabilisation report documents |

## 3. Structural Fit

✅ `App\\Extensions\\` removal is correct — it caused dual-mapped namespace collisions  
✅ Migration guards prevent fresh-install failures from duplicate table creation  
✅ Model deduplication uses `@deprecated` annotation on non-canonical duplicates (preserved, not deleted)  
✅ Source quarantine is cosmetic (CodeToUse/ organizational only)

**Adjustment vs. original PR:** The quarantine operation in PR #240 used `git mv` internally.
In this merge pass, the equivalent `cp -r` + `git rm --cached` + `git add` approach was used
due to commit signing requirements. Functionally identical outcome.

## 4. Code Quality

| Aspect | Assessment |
|---|---|
| Migration guards | Correct `Schema::hasTable()` pattern (consistent with existing guards) |
| `InspectionInstance` namespace | Changed to canonical `App\\Models\\Inspection\\InspectionInstance` |
| `SiteAsset` namespace | Canonical `App\\Models\\Facility\\SiteAsset` preserved |
| `EquipmentWarranty` reference | Updated to canonical `SiteAsset` |
| `@deprecated` markers | Non-canonical `Work\\InspectionInstance` and `Work\\SiteAsset` annotated |

## 5. Conflict Review

No git conflicts.

**Semantic:** `Premises.php` is modified by both PR #240 (namespace fix) and PR #238 (new relationship).
Both changes were applied without conflict:
- PR #240: `use App\\Models\\Work\\InspectionInstance` → `use App\\Models\\Inspection\\InspectionInstance`
- PR #238: Added `serviceAgreements()` HasMany method

## 6. Merge Decision

**MERGED WITH MINOR ADJUSTMENT** — Applied via patch/checkout for non-quarantine files + 
bulk directory move for quarantine. Functionally equivalent to direct merge.

## 7. Gap Analysis

| Gap | Severity | Next Pass |
|---|---|---|
| `CodeToUse/_Quarantine/AI_aicore_titancore_shadow_copy` — full host repo shadow | High | Delete after verification |
| Non-canonical `Work\\InspectionInstance` still exists with `@deprecated` | Low | Deletion pass |
| Non-canonical `Work\\SiteAsset` still exists with `@deprecated` | Low | Deletion pass |
| `CodeToUse/Voice/TitanVoiceSuite_Unified_Merged_From_Largest_Base` retained but unreviewed | Medium | Voice integration pass |
| `CodeToUse/Voice/TitanOmni Complete Pass26 HARDENED` retained but unreviewed | Medium | Voice integration pass |
| Route loader stabilisation notes referenced but no code changes | Low | Route audit pass |
