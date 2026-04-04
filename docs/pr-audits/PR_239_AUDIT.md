# PR #239 — Merge FSM Modules: fieldservice_sale_agreement + fieldservice_sale_recurring

**Status:** MERGED (Audit Pass 2 — 2026-04-04)  
**Risk Level:** Medium  
**Domain:** FSM / Work / Contract Lifecycle

## 1. Purpose

Introduces the `FieldServiceAgreement` contract entity, linking the commercial lifecycle:
`Quote → FieldServiceAgreement → ServicePlanVisits → ServiceJobs → Invoices`.

Enables subscription cleaning, maintenance contracts, and SLA-style recurring execution workflows.

## 2. Scope

| Area | Files |
|---|---|
| New model | `app/Models/Work/FieldServiceAgreement.php` |
| New service | `app/Services/Work/FieldServiceAgreementService.php` |
| New controller | `app/Http/Controllers/Core/Work/FieldServiceAgreementController.php` |
| Modified models | `ServiceJob`, `ServicePlanVisit`, `Customer` (relationship additions) |
| Modified controller | `PortalController` (agreement portal methods) |
| 6 new events | `FieldServiceAgreementCreated/Updated/Activated/Expired/Renewed/Cancelled` |
| Migration | `2026_04_03_500600_create_field_service_agreements_table.php` |
| Views | `fsm_agreements/index`, `fsm_agreements/show`, `portal/agreements/index`, `portal/agreements/show` |
| Routes | `routes/core/work.routes.php` + `routes/core/portal.routes.php` |
| Test | `tests/Feature/FieldServiceAgreementTest.php` |

## 3. Structural Fit

✅ `FieldServiceAgreement` is distinct from existing `ServiceAgreement` (different lifecycle/purpose)  
✅ Follows existing FSM model conventions (`BelongsToCompany`, `HasFactory`)  
✅ Routes follow `dashboard.work.fsm-agreements.*` naming convention  
✅ Portal methods follow existing `PortalController` pattern  
✅ Customer model extended with `activeServiceContracts()` and `expiringServiceContracts()`

**Note:** `ServiceJob.contract_visit_id` and `ServiceJob.recurring_source_id` are new nullable columns
requiring migration. Migration `500600` correctly defines these columns.

## 4. Code Quality

| Aspect | Assessment |
|---|---|
| Model | Complete with BelongsTo/HasMany relationships, fillable, casts |
| Service | 6 lifecycle transition methods with event emission |
| Controller | CRUD + state transitions (activate/renew/cancel) |
| Views | Basic Blade views for dashboard and portal |
| Tests | Feature test covering create/activate/renew/cancel lifecycle |

## 5. Conflict Review

No git conflicts. Files checked out cleanly.

**Semantic overlap with PR #238:**  
- Both modify `ServiceJob.php` and `ServicePlanVisit.php`  
- PR #239 adds `contractVisit()` / `recurringAgreement()` to ServiceJob  
- PR #238 adds `vehicleAssignments()` / `vehicleStockItems()` to ServiceJob  
- No method name overlap — merged cleanly

**Premises.php:** Not modified by PR #239. Only PR #238 + PR #240 changes applied.

## 6. Merge Decision

**MERGED** — Clean application. All changes additive. Semantic overlap with PR #238 resolved without conflict.

## 7. Gap Analysis

| Gap | Severity | Next Pass |
|---|---|---|
| Auto-invoice generation on agreement activation | Medium | Billing integration pass |
| SLA breach detection for active agreements | Medium | TitanContracts integration |
| Portal authentication/authorization hardening | Low | Security pass |
| Agreement renewal notification emails | Low | Comms pass |
| UI: agreement calendar view | Low | UI pass |
| `Customer.activeServiceContracts()` performance (no index hint) | Low | DB optimization pass |
