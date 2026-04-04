# Gap and Regression Report
**Generated:** 2026-04-04 | **Severity:** Critical > High > Medium > Low

---

## CRITICAL GAPS

### CRITICAL-01: TitanMesh (MODULE 10) — False Positive in Status File

**Severity:** Critical
**Type:** Lost / Regression

The `fsm_module_status.json` explicitly marks `titan_mesh` as `"status": "installed"`. Agent memories also describe it as installed with 5 models, 5 services, 6 events, 3 listeners, and 2 controllers.

**Evidence found:**
- ❌ No `app/Models/Mesh/` directory
- ❌ No `routes/core/mesh.routes.php`
- ❌ No migration matching `001000` or `mesh`
- ❌ No `app/Services/Mesh/` directory
- ❌ No mesh-related controller files

**Risk:** Any code that gates on `titan_mesh=installed` in the FSM status file will behave incorrectly.
**Action Required:** Either implement TitanMesh or remove the false `installed` status from fsm_module_status.json.

---

### CRITICAL-02: Duplicate Migration Timestamps — Risk of Artisan Migration Failure

**Severity:** Critical
**Type:** Infrastructure Risk

The following timestamp groups have 2–4 migrations with identical prefixes. Laravel uses migration filenames to determine order; when two files share a timestamp, migration order is non-deterministic and `migrate:fresh` may fail or produce inconsistent schema state.

```
2026_03_31_100900 → create_knowledge_base_tables + add_bank_account_id_to_payments_table
2026_03_31_200000 → create_service_area_regions_table + create_territory_hierarchy_tables
2026_03_31_200100 → create_service_area_districts_table + add_territory_to_sites
2026_04_02_000100 → create_service_plan_tables + enhance_premises_domain
2026_04_02_000200 → add_premises_to_service_agreements + create_facility_documents_table
2026_04_02_000300 → create_occupancies_table + create_site_assets_table
2026_04_02_000400 → create_inspection_domain_tables + create_inspection_tables
2026_04_03_700100 → create_timesheet_submissions_table + create_inventory_domain_tables
2026_04_03_800100 → create_security_domain_tables + create_dispatch_tables + add_titan_chat_surface + add_company_id_to_tz_audit_log (4-WAY COLLISION)
```

The `2026_04_03_800100` timestamp has **4 migrations** — this is a critical collision.

**Action Required:** Rename colliding migrations to unique sequential timestamps.

---

### CRITICAL-03: CodeToUse/ Source Bundles Are All Empty

**Severity:** Critical
**Type:** Architecture Gap

All `CodeToUse/` domain folders contain only `.gitkeep` files:
- CodeToUse/AI/, CRM/, Comms/, Dispatch/, Extensions/, FSM/, Finance/, Jobs/, Lifecycle/, Mobile/, Nexus/, Node/, Omni/, PWA/, Routing/, Scheduling/, Signals/, Tenancy/, UI/, Utilities/, Voice/, WorkCore/

This means the source bundle extraction system has no source material. Any agent instructions referencing extraction from `CodeToUse/` have no actual source to draw from.

**Action Required:** Either deposit actual source code bundles or clarify that direct integration (not extract-then-integrate) is the workflow.

---

### CRITICAL-04: app/Titan/Core/ vs app/TitanCore/ — Structural Duplication

**Severity:** Critical
**Type:** Duplicated / Namespace Confusion

Two parallel directory trees exist:
- `app/Titan/Core/` — contains: Agents, Contracts, Mcp, Omni, Pulse, Registry, Vector, Zero
- `app/TitanCore/` — contains: Agents, Chat, Contracts, Events, MCP, Omni, Pulse, Registry, Support, Zero, Zylos

These are not the same structure. Some subdirectory names overlap (Agents, Contracts, Omni, Pulse, Registry, Zero). There is no documentation indicating which is canonical.

**Risk:** Namespace confusion, duplicate class definitions, unclear service container bindings.
**Action Required:** Clarify canonical namespace, migrate one tree to the other, delete the duplicate.

---

## HIGH GAPS

### HIGH-01: FSM Modules 9, 10, 14, 15, 17, 19, 24, 25, 27, 28, 29, 30 — All Pending

**Severity:** High
**Type:** Incomplete Integration

The `fsm_module_status.json` 30-module roadmap has these modules marked `pending` with no existing domain code:

- `fieldservice_calendar` (M09) — No calendar-specific FSM models
- `fieldservice_recurring` (M10) — No recurring domain (only sale-recurring which is different)
- `fieldservice_geoengine` (M14) — No geo/routing engine
- `fieldservice_timeline` (M15) — No timeline module
- `fieldservice_partner_multi_relation` (M17) — No multi-partner relation
- `fieldservice_account_analytic` (M19) — No analytic accounting
- `fieldservice_maintenance` (M24) — No maintenance scheduling
- `fieldservice_mrp` (M25) — No MRP
- `fieldservice_location_geolocalize` (M27) — No geolocation service
- `fieldservice_distribution` (M28) — No distribution module
- `fieldservice_change_management` (M29) — No change management
- `fieldservice_quality` (M30) — No quality management

**Action Required:** Define merge plan for each pending module or remove from roadmap.

---

### HIGH-02: Nexus Architecture (145+ Docs) — Zero Code Implementation

**Severity:** High
**Type:** Docs Only

The `docs/nexuscore/` directory contains 145+ strategic architecture docs describing a 10-phase conversion of the platform into a 5-mode multi-purpose system. This represents significant planning work.

**Evidence of implementation:** None found in canonical branch.
- No route renames matching Nexus maps
- No controller realignment
- No mode-based service containers
- No entity registry implementation

**Risk:** Docs describe a future state that doesn't exist. Any agent acting on these docs may conflict with actual code state.

---

### HIGH-03: TitanCore/Zylos and MCP Handlers — Present But Not Wired

**Severity:** High
**Type:** Partially Present / Not Wired

Files in `app/TitanCore/Zylos/`, `app/TitanCore/MCP/Handlers/` exist. Route file `routes/core/mcp.routes.php` exists.

But there is no evidence of:
- EventServiceProvider registrations for TitanCore events
- AppServiceProvider or TitanCoreServiceProvider binding of Zylos/MCP classes to the container
- Any controller in `app/Http/Controllers/` routing to these handlers
- Any service facade or alias for the Zylos bridge

**Action Required:** Audit TitanCoreServiceProvider to confirm which TitanCore sub-systems are actually bound.

---

### HIGH-04: Omni/Comms System — Zip File in Docs, No Code

**Severity:** High
**Type:** Docs Only

`docs/omni/` contains `Titan_Omni_Master_Docs_Pass05_Wiring_Blueprint.zip` — a compressed archive that was never extracted. The Omni/Comms wiring blueprint exists only as an unextracted zip.

**Action Required:** Extract the zip, classify code, integrate or file for future sprint.

---

### HIGH-05: Inspection Domain Duplication

**Severity:** High
**Type:** Duplicated

Two parallel inspection model sets exist:
- `app/Models/Inspection/` — 7 models (InspectionInstance, InspectionTemplate, InspectionItem, InspectionSchedule, InspectionResponse, InspectionAttachment, InspectionTemplateItem)
- `app/Models/Work/InspectionInstance.php` — separate InspectionInstance in Work namespace

Two parallel migration files at timestamp `2026_04_02_000400`:
- `create_inspection_domain_tables.php`
- `create_inspection_tables.php`

**Risk:** Schema collision — both likely create similar tables. Only one should be canonical.
**Action Required:** Determine which is canonical, remove the duplicate.

---

### HIGH-06: EventServiceProvider — Multiple Event Families Not Registered

**Severity:** High
**Type:** Missing Event Wiring

The following event families exist in `app/Events/` but their registration in EventServiceProvider needs verification:

Events **likely registered** (Module 01-09 pattern):
- Work events (core set)
- Equipment events
- Route events
- Trust events
- Sync events
- TimeGraph events
- Finance events
- Docs events
- Predict events
- Team events

Events **potentially missing** (newer additions):
- `app/Events/Repair/` — 25+ repair events
- `app/Events/Inspection/` — 6 events
- `app/Events/Premises/` — 2 events (HazardDetected/Resolved)
- `app/Events/Crm/` — 7 CRM events
- `app/Events/Work/Portal*` — 6 portal events
- `app/Events/Work/FieldServiceProject*` — 5 project events
- `app/Events/Work/Vehicle*` — newer vehicle events

**Action Required:** Cross-check all event files against EventServiceProvider `$listen` array.

---

### HIGH-07: AvailabilityWindow Duplication

**Severity:** High
**Type:** Duplicated

Two AvailabilityWindow models exist:
- `app/Models/Route/AvailabilityWindow.php` — Route domain
- `app/Models/Team/AvailabilityWindow.php` — Team/Capability domain

These are likely the same concept or overlapping. Tables may conflict.

---

### HIGH-08: SiteAsset Duplication

**Severity:** High
**Type:** Duplicated

Two SiteAsset models exist:
- `app/Models/Work/SiteAsset.php`
- `app/Models/Facility/SiteAsset.php`

Two SiteNote models may also conflict with Work/SiteNote.

---

## MEDIUM GAPS

### MED-01: TitanMesh FSM Status — False Entry Must Be Corrected

The `fsm_module_status.json` says TitanMesh is `installed`. This is incorrect based on code audit. Status must be corrected to `pending` or `planned`.

---

### MED-02: Module Reports Promise Features Not in Code

Several module reports in `docs/modules/` describe wiring steps that may not be complete:
- MODULE_10_TitanMesh_report.md describes routes, controllers, and models that don't exist in canonical branch

---

### MED-03: Repair Events Not in EventServiceProvider

`app/Events/Repair/` contains 25+ events. Most repair events likely need listeners. The EventServiceProvider needs to be audited to confirm all repair events are registered.

---

### MED-04: WorkCore Provider — Scope Unclear

`app/Providers/WorkCoreServiceProvider.php` exists but its bindings and what it registers is not confirmed. No WorkCore-specific services are identifiable.

---

### MED-05: Finance Duplication — FinancialRollup in Two Namespaces

- `app/Models/Finance/FinancialRollup.php` — Finance domain
- `app/Models/Money/...` — Money domain has overlapping financial concepts

The Finance and Money service layers (app/Services/Finance/ vs app/Services/TitanMoney/) are parallel. The boundary between them is not clearly documented.

---

### MED-06: Jobs.php Root Model vs Work/ServiceJob

`app/Models/Jobs.php` exists at root level (likely legacy queue jobs model) alongside `app/Models/Work/ServiceJob.php`. The naming is potentially confusing but these are separate concepts.

---

### MED-07: Inspector Instances — Three Levels

`InspectionInstance` appears in:
- `app/Models/Inspection/InspectionInstance.php`
- `app/Models/Work/InspectionInstance.php`

This should be resolved to a single model.

---

### MED-08: Route Loader Missing for mesh.routes.php

The `RouteServiceProvider::loadCoreRoutes()` globbing approach auto-loads all `routes/core/*.routes.php` files. Since `mesh.routes.php` doesn't exist, the TitanMesh routing would fail silently even if models were added.

---

### MED-09: CodeToUse AGENTS.md Rules Not Executable

`CodeToUse/AGENTS.md` defines rules for agent use of source bundles, but all the bundles are empty. The ruleset describes a workflow with no source material.

---

## LOW GAPS

### LOW-01: Omni Blueprint is a Zip Not Extracted

The `docs/omni/Titan_Omni_Master_Docs_Pass05_Wiring_Blueprint.zip` is an unextracted archive. Its contents are unknown.

### LOW-02: Mobile Apps Referenced But Not Present

Five mobile apps (TitanCommand, TitanGo, TitanPortal, TitanMoney, TitanPro) are mentioned in `docs/MOBILE_STACK_ALIGNMENT.md` but no mobile app code exists in the repository.

### LOW-03: TitanCore Zero Subdirectories Very Deep

`app/TitanCore/Zero/` has 9 subdirectories (AI, Budget, Knowledge, Memory, Process, Rewind, Signals, Skills, Telemetry). Without confirmed wiring, these may be stubs.

### LOW-04: docs/nexuscore/engine-docs-pass1-16

This subdirectory exists but its contents were not fully enumerated. It likely contains more architecture docs in the Nexus conversion planning era.

### LOW-05: WORKCore Root-Level Docs

Several WORKCORE_*.md files at repo root (WORKCORE_MERGE.md, WORKCORE_FEDERATION_TABLE_MAP.md, etc.) appear to be planning docs from Era 2 that have not been moved to docs/.

### LOW-06: Insights Route File

`routes/core/insights.routes.php` exists but no corresponding `app/Http/Controllers/` subdirectory for insights was found. This route may be orphaned.

---

## REGRESSION TRACKING

| Regressed Feature | Last Known State | Current State |
|-------------------|-----------------|---------------|
| TitanMesh | Marked installed in fsm_module_status.json | NO CODE IN BRANCH |
| CodeToUse source bundles | Referenced as extraction source | ALL EMPTY |
| Nexus 10-phase conversion | Fully planned in docs | NOT STARTED |
| Omni wiring blueprint | Documented in zip | NOT EXTRACTED |
| Mobile apps | Referenced in MOBILE_STACK_ALIGNMENT | NOT PRESENT |
