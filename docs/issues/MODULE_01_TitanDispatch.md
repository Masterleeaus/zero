# MODULE 01 — TitanDispatch: Constraint Solver Dispatch Engine

**Label:** `titan-module` `dispatch` `scheduling` `ai-core`
**Milestone:** TitanZero Capability Expansion Pack v1
**Priority:** High

---

## Overview

Build the **TitanDispatch** engine — a constraint-solving, AI-assisted job dispatch system that allocates technicians to service jobs based on skills, availability, territory, travel cost, and SLA urgency. This module replaces manual dispatcher decision-making with a governed, explainable, signal-driven allocation layer.

TitanDispatch must integrate with the existing `ServiceJob`, `ServicePlan`, `Territory`, and `Branch` models, emit Titan Signals on every allocation decision, and record full audit trails via `AuditTrail`.

---

## Pre-Scan Requirement (MANDATORY — run before any implementation pass)

Before writing a single line of code, the implementing agent MUST perform the following 10-step scan:

1. Read `app/Models/Work/ServiceJob.php` — understand all fields, relationships, status FSM
2. Read `app/Models/Work/ServicePlan.php` — understand frequency, interval, next_visit_due
3. Read `app/Models/Work/Territory.php`, `Branch.php`, `District.php`, `Region.php`, `Zone.php`
4. Read `app/Models/Team/` — all technician/user models present
5. Read `app/Titan/Signals/` — all 21 signal system files, understand SignalDispatcher, ProcessStateMachine, AuditTrail
6. Read `app/Services/Work/JobStageService.php` — understand stage transition pattern
7. Read `database/migrations/` — identify all `service_jobs`, `service_plans`, `territories` table structures
8. Read `docs/titancore/` — scan for any dispatch, scheduling, or allocation design docs
9. Read `docs/nexuscore/DOC38_Master_Development_Roadmap.md` — confirm module sequencing
10. Read `app/Events/Work/` — all 16 work events to understand event patterns before creating new ones

---

## Canonical Models to Extend / Reference

- `app/Models/Work/ServiceJob.php` — primary dispatch target
- `app/Models/Work/ServicePlan.php` — source of scheduled demand
- `app/Models/Work/Territory.php` — geographic allocation boundary
- `app/Models/Work/Branch.php` — organisational dispatch scope
- `app/Models/Work/JobType.php` — skill requirement mapping source
- `app/Models/Equipment/InstalledEquipment.php` — asset-based dispatch triggers
- `app/Models/Premises/Premises.php` — site location for travel-cost calculation

---

## Required Output Artifacts

### Migrations
- `database/migrations/YYYY_MM_DD_create_dispatch_tables.php`
  - `dispatch_assignments` — stores allocation decisions: `job_id`, `technician_id`, `assigned_by` (user|ai), `constraint_score`, `travel_estimate_mins`, `assigned_at`, `confirmed_at`, `status`
  - `dispatch_constraints` — configurable constraint rules: `company_id`, `constraint_type` (skill|territory|availability|sla|travel_cost), `weight`, `is_active`
  - `dispatch_queue` — pending unallocated jobs: `job_id`, `priority_score`, `queued_at`, `attempts`, `last_attempt_at`
- All tables use `Schema::hasTable()` / `Schema::hasColumn()` guards

### Models
- `app/Models/Work/DispatchAssignment.php` — with `BelongsToCompany`, `BelongsTo(ServiceJob)`, `BelongsTo(User, 'technician_id')` relationships
- `app/Models/Work/DispatchConstraint.php` — configurable rule model
- `app/Models/Work/DispatchQueue.php` — queue entry model

### Services
- `app/Services/Work/DispatchService.php`
  - `allocate(ServiceJob $job): DispatchAssignment` — main solver entry point
  - `scoreCandidate(User $tech, ServiceJob $job, array $constraints): float`
  - `buildCandidatePool(ServiceJob $job): Collection`
  - `queueForDispatch(ServiceJob $job): DispatchQueue`
  - `confirmAssignment(DispatchAssignment $assignment): void`
  - `reDispatch(ServiceJob $job, string $reason): DispatchAssignment`
- `app/Services/Work/DispatchConstraintService.php`
  - `loadConstraints(int $companyId): Collection`
  - `evaluateSkillMatch(User $tech, ServiceJob $job): float`
  - `evaluateTerritoryMatch(User $tech, Premises $premises): float`
  - `evaluateSlaUrgency(ServiceJob $job): float`

### Events
- `app/Events/Work/JobDispatched.php` — fired on successful allocation
- `app/Events/Work/JobDispatchFailed.php` — fired when no candidate found
- `app/Events/Work/JobReDispatched.php` — fired on re-allocation

### Listeners
- `app/Listeners/Work/RecordDispatchAuditTrail.php` — writes to `AuditTrail` on every dispatch signal
- `app/Listeners/Work/NotifyTechnicianOfAssignment.php` — hooks into notification system

### Signals
- Emit `TitanSignal` via `app/Titan/Signals/SignalDispatcher.php` for each dispatch action
- Signal types: `dispatch.allocated`, `dispatch.failed`, `dispatch.reassigned`, `dispatch.confirmed`
- Record signal context: job_id, technician_id, constraint_scores, reasoning

### Controllers / Routes
- `app/Http/Controllers/Work/DispatchController.php`
  - `index()` — dispatch queue dashboard
  - `assign(Request $request, ServiceJob $job)` — manual override
  - `autoDispatch(ServiceJob $job)` — trigger AI allocation
  - `history(ServiceJob $job)` — assignment history
- Register in `routes/core/work.php` under `dispatch` prefix

### Tests
- `tests/Unit/Services/Work/DispatchServiceTest.php`
- `tests/Feature/Work/DispatchControllerTest.php`

### Docs Report
- `docs/modules/MODULE_01_TitanDispatch_report.md` — overlap map, FSM states added, signal catalogue, constraint schema

### FSM Update
- Update `fsm_module_status.json` — set `titan_dispatch` to `installed`

---

## Architecture Notes

- Constraint solver must be deterministic and loggable — every score must be persisted
- AI override must be a separate code path from rule-based allocation, both must emit the same signals
- Re-dispatch must increment `attempts` in `dispatch_queue` and flag jobs exceeding `max_attempts` threshold
- Must respect `company_id` scoping throughout — no cross-tenant data leakage
- ServiceJob status transitions (unassigned → assigned → confirmed) must flow through `JobStageService`
- Follow existing event registration pattern in `app/Providers/EventServiceProvider.php`

---

## References

- `app/Titan/Signals/SignalDispatcher.php`
- `app/Titan/Signals/ProcessStateMachine.php`
- `app/Titan/Signals/AuditTrail.php`
- `app/Services/Work/JobStageService.php`
- `app/Events/Work/` (all 16 events as pattern reference)
- `docs/nexuscore/` (full directory — scan for scheduling/dispatch docs)
- `docs/titancore/` (full directory — scan for FSM and signal design)
- `database/migrations/2026_04_02_000800_create_service_plan_tables.php`
- `CodeToUse/work/` (if present — scan for dispatch-related entity files)
