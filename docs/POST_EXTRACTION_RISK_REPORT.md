# POST_EXTRACTION_RISK_REPORT.md

**Phase 8 — Step 10: Post-Extraction Integration Risk Summary**
**Date:** 2026-04-03
**Audit Type:** Structural Integrity — Mapping and Verification Only
**Scope:** Full repository post-extraction scan

---

## 1. Executive Summary

A large extraction pass created approximately 62,000 files from CodeToUse ZIP sources. This audit confirms that the **host system (app/, routes/, database/migrations/) has pre-existing structural issues introduced by earlier integration passes**, and that `CodeToUse/` contains **significant duplication, competing versions, and latent namespace conflicts** that must be resolved before any further integration begins.

**The host application is functional** but contains unresolved migration conflicts that will cause failures on a fresh database install.

**No refactors, renames, or deletions were performed in this audit pass.**

---

## 2. Critical Findings (Block Integration)

These issues must be resolved before any new CodeToUse integration begins:

| ID | Category | Finding | Document |
|----|----------|---------|----------|
| C-01 | Migration | `tz_signals` table created in 2 migrations — **will fail on fresh install** | MIGRATION_COLLISION_MAP.md |
| C-02 | Migration | `tz_rewind_snapshots` table created in 2 migrations — **will fail on fresh install** | MIGRATION_COLLISION_MAP.md |
| C-03 | Migration | `inspection_instances` created in 2 same-timestamp migrations | MIGRATION_COLLISION_MAP.md |
| C-04 | Model | `SiteAsset` model exists in both `App\Models\Facility\` and `App\Models\Work\` — same table | MODEL_COLLISION_MAP.md |
| C-05 | Model | `InspectionInstance` model exists in both `App\Models\Inspection\` and `App\Models\Work\` — scheduling surface bound to one only | MODEL_COLLISION_MAP.md |
| C-06 | Autoload | `App\Extensions\` PSR-4 dual-mapping — `app/Extensions/` and `CodeToUse/` both serve this namespace | AUTOLOAD_CONFLICT_MAP.md |
| C-07 | Source | `CodeToUse/AI/aicore/titancore/` contains a shadow copy of the host repository | SOURCE_DUPLICATE_ENGINE_MAP.md |
| C-08 | Source | `CodeToUse/AI/aicore/AICores/` is an exact duplicate of `CodeToUse/AI/AICores/` | SOURCE_DUPLICATE_ENGINE_MAP.md |

---

## 3. High-Risk Findings (Resolve Before Feature Integration)

| ID | Category | Finding | Document |
|----|----------|---------|----------|
| H-01 | Migration | `service_plan_checklists` created in both migration 000100 and 000800 | MIGRATION_COLLISION_MAP.md |
| H-02 | Migration | `asset_service_events` created in both migration 000300 and 000600 | MIGRATION_COLLISION_MAP.md |
| H-03 | Migration | 7+ identical Voice migration sets in CodeToUse — will collide if more than one activated | MIGRATION_COLLISION_MAP.md |
| H-04 | Provider | 9+ competing copies of Chatbot provider suite in CodeToUse/Voice | PROVIDER_COLLISION_MAP.md |
| H-05 | Autoload | GraphQL vendor bundle in `CodeToUse/Comms/ably-archive/` shadows webonyx package | AUTOLOAD_CONFLICT_MAP.md |
| H-06 | Extension | 5 extension slugs exist in both ExtensionLibrary and Voice passes — registration collision | EXTENSION_BOUNDARY_AUDIT.md |
| H-07 | Extension | `CheckoutRegistration`, `AiChatProFolders`, `LiveCustomizer` write into core tables | EXTENSION_BOUNDARY_AUDIT.md |
| H-08 | Mobile | TitanCommand, TitanMoney, TitanPro share same Flutter package name `demandium_provider` | MOBILE_STACK_ALIGNMENT.md |
| H-09 | Mobile | All 5 mobile apps use `YOUR_BASE_URL` placeholder — not configured | MOBILE_STACK_ALIGNMENT.md |
| H-10 | Model | ~2,745 CodeToUse model files declare `App\Models\*` namespace — mass conflict if activated | MODEL_COLLISION_MAP.md |
| H-11 | Source | 11 versions of Voice Suite exist — only ONE canonical version for integration | SOURCE_DUPLICATE_ENGINE_MAP.md |
| H-12 | Route | Glob loader auto-includes any `.routes.php` in `routes/core/` — no explicit control | ROUTE_LOADER_DRIFT.md |
| H-13 | Route | CodeToUse/Signals routes conflict with active `signals.routes.php` | ROUTE_LOADER_DRIFT.md |

---

## 4. Medium-Risk Findings (Track and Resolve Per Domain)

| ID | Category | Finding | Document |
|----|----------|---------|----------|
| M-01 | Migration | `service_jobs` table modified in 10+ migrations — duplicate column risk | MIGRATION_COLLISION_MAP.md |
| M-02 | Migration | Same-timestamp territory migration files ordering risk | MIGRATION_COLLISION_MAP.md |
| M-03 | Provider | CodeToUse/Signals provider would conflict with TitanSignalsServiceProvider | PROVIDER_COLLISION_MAP.md |
| M-04 | Provider | CodeToUse/WorkCore titancore bootstrap may rebind WorkCoreServiceProvider | PROVIDER_COLLISION_MAP.md |
| M-05 | Controller | Root-level `AIController`, `AIChatController`, `TTSController` may be dead (shadowed) | CONTROLLER_NAMESPACE_DRIFT.md |
| M-06 | Controller | Route files must use fully-qualified class names to avoid same-name ambiguity | CONTROLLER_NAMESPACE_DRIFT.md |
| M-07 | Model | Tenant scope (`BelongsToCompany`) coverage unverified on Facility/Inspection models | MODEL_COLLISION_MAP.md |
| M-08 | Model | CodeToUse/WorkCore uses legacy `WorkCore\*` namespace paths | MODEL_COLLISION_MAP.md |
| M-09 | Extension | `Maintenance` and `External-Chatbot` may register global middleware | EXTENSION_BOUNDARY_AUDIT.md |
| M-10 | Extension | `CodeToUse/WorkCore/titancore/routes/web.php` could overwrite host routing if naively merged | EXTENSION_BOUNDARY_AUDIT.md |
| M-11 | Mobile | `CodeToUse/Mobile/` is a redundant mirror of `mobile_apps/` | MOBILE_STACK_ALIGNMENT.md |
| M-12 | Source | `AI/AiSocialMedia` (v4.5.0) is stale — `Comms/SocialMedia` (v5.1.0) is canonical | SOURCE_DUPLICATE_ENGINE_MAP.md |
| M-13 | Source | CRM `leads` domain has two parallel versions | SOURCE_DUPLICATE_ENGINE_MAP.md |
| M-14 | Autoload | `Modules\` namespace used in CodeToUse tests but not registered in composer | AUTOLOAD_CONFLICT_MAP.md |

---

## 5. Low-Risk Findings (Monitor)

| ID | Category | Finding | Document |
|----|----------|---------|----------|
| L-01 | Autoload | `AdsenseService.php` in `autoload.files` — non-standard pattern | AUTOLOAD_CONFLICT_MAP.md |
| L-02 | Provider | All currently registered providers appear clean — no duplicate registrations | PROVIDER_COLLISION_MAP.md |
| L-03 | Migration | CodeToUse extension migrations are inactive but would conflict when activated | MIGRATION_COLLISION_MAP.md |
| L-04 | Route | No double-loader execution detected in current core | ROUTE_LOADER_DRIFT.md |
| L-05 | Route | No duplicate named routes detected in current active route files | ROUTE_LOADER_DRIFT.md |
| L-06 | Controller | All duplicates within `app/Http/Controllers` are in different sub-namespaces — no PHP collision | CONTROLLER_NAMESPACE_DRIFT.md |
| L-07 | Controller | TitanRewind extension controllers are clean and isolated | CONTROLLER_NAMESPACE_DRIFT.md |
| L-08 | Model | Finance vs Money namespace separation may cause confusion | MODEL_COLLISION_MAP.md |
| L-09 | Extension | TitanRewind only migration collision: `tz_rewind_snapshots` (covered by C-02) | EXTENSION_BOUNDARY_AUDIT.md |
| L-10 | Mobile | No Signal/Omni endpoint duplication in mobile apps yet | MOBILE_STACK_ALIGNMENT.md |
| L-11 | Source | `CodeToUse/Routing/` is empty | SOURCE_DUPLICATE_ENGINE_MAP.md |
| L-12 | Source | `CodeToUse/Signals/` appears already integrated — safe to archive | SOURCE_DUPLICATE_ENGINE_MAP.md |

---

## 6. Module Classification

### Safe to Integrate (after resolving CRITICAL blockers)

| Module | Location | Notes |
|--------|----------|-------|
| Comms/SocialMedia (v5.1.0) | `CodeToUse/Comms/SocialMedia/` | Use this over AI/AiSocialMedia v4.5 |
| Social Media Agent | `CodeToUse/AI/SocialMediaAgent/` | Distinct extension, no conflicts found |
| TitanTrust | `CodeToUse/Tenancy/TitanTrust/` | Tenancy trust layer — not yet integrated |
| FSM/inventory | `CodeToUse/FSM/inventory/` | Verify against existing Inventory domain |
| Tenancy/compliance-auditing | `CodeToUse/Tenancy/compliance-auditing/` | New domain, no conflicts detected |
| Extensions/Canvas | `CodeToUse/Extensions/ExtensionLibrary/Canvas/` | Clean extension, single migration |
| Extensions/AiChatProFolders | `CodeToUse/Extensions/ExtensionLibrary/AiChatProFolders/` | Additive to user_openai — review column conflict |
| Dispatch/easydispatch | `CodeToUse/Dispatch/easydispatch-main/` | No conflicts detected in audit |

### Needs Refactor Before Integration

| Module | Location | Required Refactor |
|--------|----------|------------------|
| WorkCore domain slices | `CodeToUse/WorkCore/work/` | Must bridge `WorkCore\*` namespace to `App\Models\Work\*` |
| Voice Suite | `TitanVoiceSuite_Unified_Merged_From_Largest_Base/` | Must choose ONE canonical version; deduplicate providers |
| CRM/demandium | `CodeToUse/CRM/demandium/` | Must bridge to existing `App\Models\Crm\*` |
| AI/AICores engines | `CodeToUse/AI/AICores/` | Must namespace-isolate before integration |
| Omni/TitanHello | `CodeToUse/Omni/TitanOmni/` | Requires Omni route isolation |
| Extensions/CheckoutRegistration | `CodeToUse/Extensions/` | Must refactor to not alter `users` table column if already present |
| Extensions/LiveCustomizer | `CodeToUse/Extensions/` | Must verify menu migration compatibility |

### Quarantine (Do Not Integrate Until Audited)

| Module | Location | Reason |
|--------|----------|--------|
| `CodeToUse/AI/aicore/titancore/` | Shadow host copy | Contains full Laravel app — identity conflict with host |
| `CodeToUse/Comms/ably-archive/` | Vendor dump | Contains GraphQL/WpOrg vendor copies — namespace pollution |
| All Voice Suite passes (Pass1–Pass11) | `CodeToUse/Voice/` | Superseded by Unified/Pass26 — retain only canonical |
| `AI/AiSocialMedia/` v4.5 | `CodeToUse/AI/AiSocialMedia/` | Superseded by Comms/SocialMedia v5.1 |
| `CRM/leads/Lead/` | `CodeToUse/CRM/leads/Lead/` | Determine if superseded by `leads/leads/` version |

### Archive Only (Already Integrated)

| Module | Location | Reason |
|--------|----------|--------|
| Signals base | `CodeToUse/Signals/titan_signal/` | TitanSignalsServiceProvider already in host |
| FSM modules 1–23 | Earlier CodeToUse/FSM source material | Already merged into host via Phase 4–7 passes |
| `CodeToUse/AI/aicore/AICores/` | Exact duplicate of `AI/AICores/` | Archive this copy |
| `CodeToUse/Mobile/` | Mirror of `mobile_apps/` | Archive this copy |

---

## 7. Recommended Next Steps (Ordered)

> **IMPORTANT: This is a mapping report. No changes have been made.**
> The following are recommendations for subsequent work passes.

1. **FIX CRITICAL MIGRATIONS** — Resolve C-01 through C-03: Remove duplicate `Schema::create` calls from `2026_03_31_000100_add_federation_metadata_and_tables.php` for `tz_signals` and `tz_rewind_snapshots`. Resolve the same-timestamp `inspection_instances` conflict.

2. **RESOLVE MODEL DUPLICATES** — Fix C-04 and C-05: Consolidate `SiteAsset` to one namespace; consolidate `InspectionInstance` to one namespace with `SchedulableEntity` interface.

3. **CLARIFY AUTOLOAD MAP** — Fix C-06: Decide if `CodeToUse/` should be under `App\Extensions\` or a separate namespace root (e.g., `TitanSource\` or `CodeLibrary\`).

4. **ARCHIVE SHADOW COPIES** — Address C-07, C-08, and all identified duplicates: Archive `aicore/titancore/`, `aicore/AICores/`, `Comms/ably-archive/`, `CodeToUse/Mobile/`.

5. **SELECT CANONICAL VOICE BUNDLE** — Address H-04, H-11: Choose one Voice Suite version for integration. Recommended: `TitanVoiceSuite_Unified_Merged_From_Largest_Base/` or `TitanOmni Complete Pass26 HARDENED/`.

6. **EXTENSION INTEGRATION SAFETY** — Address H-06, H-07: For each CodeToUse extension, verify table alteration is additive and safe before activation.

7. **MOBILE CONFIGURATION** — Address H-08, H-09: Align Flutter package names, configure `baseUrl` for Titan Zero deployment.

---

## 8. Audit Document Index

| Document | Coverage |
|----------|---------|
| `docs/AUTOLOAD_CONFLICT_MAP.md` | PSR-4 conflicts, namespace pollution, vendor shadows |
| `docs/PROVIDER_COLLISION_MAP.md` | Service provider duplicates, middleware conflicts |
| `docs/MIGRATION_COLLISION_MAP.md` | Duplicate table creation, timestamp ordering, foreign key risks |
| `docs/ROUTE_LOADER_DRIFT.md` | Route loader gaps, glob safety, API shadowing |
| `docs/CONTROLLER_NAMESPACE_DRIFT.md` | Controller duplicates, dead controllers, namespace mismatches |
| `docs/MODEL_COLLISION_MAP.md` | Model name collisions, table overlaps, tenant scope |
| `docs/EXTENSION_BOUNDARY_AUDIT.md` | Extension isolation, core table writes, global middleware |
| `docs/MOBILE_STACK_ALIGNMENT.md` | Flutter target duplicates, API endpoint configuration |
| `docs/SOURCE_DUPLICATE_ENGINE_MAP.md` | Duplicate bundles, shadow copies, legacy vs canonical versions |
| `docs/POST_EXTRACTION_RISK_REPORT.md` | This document — consolidated risk classification |

---

*End of Phase 8 Post-Extraction Structural Integrity Audit.*
*No code was modified during this audit. All findings are mapped and classified only.*
