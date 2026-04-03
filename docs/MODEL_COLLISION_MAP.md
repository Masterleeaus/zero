# MODEL_COLLISION_MAP.md

**Phase 8 — Step 6: Model Collision Audit**
**Date:** 2026-04-03
**Scope:** app/Models, app/Extensions models, CodeToUse models

---

## 1. Confirmed Duplicate Model Class Names (Core — app/Models)

The following model class names exist under different namespaces within `app/Models/`:

### 1a. `SiteAsset` — HIGH RISK

| File | Namespace | Table |
|------|-----------|-------|
| `app/Models/Facility/SiteAsset.php` | `App\Models\Facility` | `site_assets` |
| `app/Models/Work/SiteAsset.php` | `App\Models\Work` | `site_assets` |

**Impact:** Both models target the same `site_assets` table. Any code importing `SiteAsset` without full qualification may bind to the wrong model. Relationships, scopes, and events may differ between the two.

**Root Cause:** The Facility domain and Work domain both extracted a SiteAsset model independently during previous integration passes.

### 1b. `InspectionInstance` — HIGH RISK

| File | Namespace | Implements |
|------|-----------|-----------|
| `app/Models/Inspection/InspectionInstance.php` | `App\Models\Inspection` | `SchedulableEntity` |
| `app/Models/Work/InspectionInstance.php` | `App\Models\Work` | No interface |

**Impact:** The `Inspection` namespace version implements `SchedulableEntity` (integrated with scheduling surface), while the `Work` namespace version does not. `SchedulingSurfaceProvider` may be bound to one but not the other.

---

## 2. Model Namespace Structure (Core)

Currently active model namespaces in `app/Models/`:

```
App\Models\                 (root — legacy platform models)
App\Models\Chatbot\
App\Models\Common\
App\Models\Concerns\        (traits — not models)
App\Models\Crm\
App\Models\Equipment\
App\Models\Facility\
App\Models\Finance\
App\Models\Frontend\
App\Models\Inspection\
App\Models\Integration\
App\Models\Meter\
App\Models\Money\
App\Models\Premises\
App\Models\Repair\
App\Models\Route\
App\Models\Section\
App\Models\Support\
App\Models\Team\
App\Models\Work\
```

**Note:** Both `App\Models\Finance\` and `App\Models\Money\` exist. Finance contains SaaS subscription models (`AiChatModelPlan`, `Subscription`, `YokassaSubscription`). Money contains business finance models (`Account`, `Invoice`, `Quote`, `Payment`). These are distinct domains — **no collision**, but naming may be confusing.

---

## 3. Tables Touched by Multiple Models

The following tables are referenced by more than one model:

| Table | Model 1 | Model 2 | Risk |
|-------|---------|---------|------|
| `site_assets` | `App\Models\Facility\SiteAsset` | `App\Models\Work\SiteAsset` | **CRITICAL** |
| `inspection_instances` | `App\Models\Inspection\InspectionInstance` | `App\Models\Work\InspectionInstance` | **CRITICAL** |

---

## 4. Tenant Scope Mismatches

Models using `BelongsToCompany` trait (auto-scopes to company_id):
- `App\Models\Crm\Customer` — confirmed
- `App\Models\Work\ServiceJob` — confirmed
- `App\Models\Money\Invoice` — needs verification

Models that may need but lack `BelongsToCompany`:
- `App\Models\Facility\SiteAsset` — **unverified**
- `App\Models\Work\SiteAsset` — **unverified**
- `App\Models\Inspection\InspectionInstance` — **unverified**

**Risk:** If the canonical model for `site_assets` lacks `BelongsToCompany`, multi-tenant data isolation breaks.

---

## 5. Legacy WorkCore vs Titan Naming Conflicts

Based on repository history and WorkCore migration documentation:

| Legacy Name | Titan Name | Status |
|------------|-----------|--------|
| `WorkCore\Job` | `App\Models\Work\ServiceJob` | Renamed — WorkCore reference may persist in CodeToUse |
| `WorkCore\Visit` | `App\Models\Work\ServicePlanVisit` | Renamed |
| `WorkCore\Site` | `App\Models\Work\Site` | Aligned |
| `WorkCore\Team` | `App\Models\Team\Team` | Aligned |
| `WorkCore\Staff` | `App\Models\Work\StaffProfile` | Added HRM pass |

`CodeToUse/WorkCore/` bundles may reference old `WorkCore\*` namespace paths that no longer exist in the host, causing fatal errors if integrated without bridging.

---

## 6. CodeToUse Model Risk (Not Yet Active)

`CodeToUse/` contains approximately **2,745 model files** declaring `App\Models\*` or extension namespaces. Key risks:

| Domain | CodeToUse Path | Conflict With Host |
|--------|---------------|-------------------|
| CRM | `CodeToUse/CRM/demandium/` | `App\Models\Crm\Customer`, `App\Models\Crm\Enquiry` |
| Jobs/Work | `CodeToUse/WorkCore/WorkCore/` | `App\Models\Work\ServiceJob` et al. |
| FSM | `CodeToUse/FSM/` | `App\Models\Work\ServiceJob`, `ServicePlan`, `ServiceAgreement` |
| Finance | `CodeToUse/Finance/` | `App\Models\Money\Invoice`, `Account` |
| Voice | `CodeToUse/Voice/` | `App\Models\Voice\ElevenlabVoice` |
| Omni | `CodeToUse/Omni/TitanOmni/` | Voice/Comms models (Call, VoiceSession, etc.) |

---

## 7. Duplicate Relationship Definitions

With `SiteAsset` and `InspectionInstance` each having two model files, relationship definitions (hasMany, belongsTo) may diverge. For example:
- `ServiceJob → hasMany(SiteAsset)` — which SiteAsset does it use?
- `Site → hasMany(InspectionInstance)` — which InspectionInstance?

Any `use` import without full namespace will bind to whichever was loaded first by Composer.

---

## 8. Summary Table

| Risk Level | Finding |
|------------|---------|
| **CRITICAL** | `SiteAsset` model exists in both `App\Models\Facility\` and `App\Models\Work\` — both target `site_assets` table |
| **CRITICAL** | `InspectionInstance` model exists in both `App\Models\Inspection\` and `App\Models\Work\` — scheduling surface uses Inspection version only |
| **HIGH** | ~2,745 model files in CodeToUse/  declare `App\Models\*` — will cause mass namespace conflicts if integrated without refactoring |
| **HIGH** | CodeToUse/WorkCore uses legacy `WorkCore\*` namespace paths — cannot integrate without bridging |
| **MEDIUM** | Tenant scope (`BelongsToCompany`) coverage needs verification on Facility and Inspection domain models |
| **MEDIUM** | Finance (`App\Models\Finance\`) and Money (`App\Models\Money\`) namespace separation may cause confusion in domain code |
| **LOW** | No cross-model relationship conflicts detected in current active models beyond duplicate-name issues |
