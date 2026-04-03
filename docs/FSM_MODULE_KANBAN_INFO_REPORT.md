# FSM Module 23: fieldservice_kanban_info — Integration Report

**Date:** 2026-04-03
**Module:** fieldservice_kanban_info
**Integration target:** WorkCore Service Job Pipeline

---

## Summary

This module extends the WorkCore job pipeline with kanban workflow intelligence.
It attaches readiness flags, SLA signals, dispatch priority scoring, blocker
management, and CRM/agreement/equipment awareness directly to the canonical
`ServiceJob` model — without creating parallel FSM domain stacks.

---

## Deliverables

### Migration

| File | Purpose |
|------|---------|
| `database/migrations/2026_04_03_500300_add_fieldservice_kanban_info_columns.php` | Extends `service_jobs` + `job_stages`; creates `fsm_job_status_meta`, `fsm_job_blockers`, `fsm_job_priority_scores` |

**service_jobs columns added:**

| Column | Type | Default | Purpose |
|--------|------|---------|---------|
| `kanban_state` | string | `normal` | Current kanban state (normal / blocked / ready_for_next_stage) |
| `kanban_state_label` | string nullable | — | Human-readable state label |
| `sla_deadline` | timestamp nullable | — | Contractual or configured SLA deadline |
| `sla_breached` | boolean | false | True once SLA deadline passes |
| `readiness_score` | smallint | 0 | Computed dispatch priority score (0–100) |

**job_stages columns added:**

| Column | Type | Default | Purpose |
|--------|------|---------|---------|
| `display_badge` | string nullable | — | Badge label shown on kanban card |
| `badge_color` | string nullable | — | Hex colour for the badge |
| `kanban_fold` | boolean | false | Fold this stage column on the kanban board |

---

### Models

| File | Namespace | Purpose |
|------|-----------|---------|
| `app/Models/FSM/FsmJobStatusMeta.php` | `App\Models\FSM` | Per-job readiness + dispatch enrichment flags |
| `app/Models/FSM/FsmJobBlocker.php` | `App\Models\FSM` | Individual blocking reason attached to a job |
| `app/Models/FSM/FsmJobPriorityScore.php` | `App\Models\FSM` | Composite dispatch priority scores |

All FSM models use `BelongsToCompany` for multi-tenant scoping.

---

### Service

| File | Namespace | Purpose |
|------|-----------|---------|
| `app/Services/FSM/KanbanStatusService.php` | `App\Services\FSM` | Core intelligence engine |

**Public API:**

```php
getJobKanbanState(ServiceJob $job): array          // full kanban payload
getDispatchPriority(ServiceJob $job): array        // EasyDispatch payload
getBlockingReasons(ServiceJob $job): Collection    // active blockers
refresh(ServiceJob $job): FsmJobStatusMeta         // force recompute + persist
addBlocker(ServiceJob, type, label, details): FsmJobBlocker
clearBlocker(FsmJobBlocker, resolvedBy): FsmJobBlocker
```

**Priority score components (weighted, 0–100 each):**

| Component | Weight | Basis |
|-----------|--------|-------|
| Urgency | 35% | job priority (urgent/high/normal/low) |
| SLA | 25% | hours remaining to sla_deadline |
| Client tier | 20% | VIP flag / customer tier attribute |
| Agreement | 10% | active service agreement present |
| Equipment | 10% | warranty job flags |

---

### Controller

| File | Route prefix |
|------|-------------|
| `app/Http/Controllers/Core/Work/KanbanStatusController.php` | `/dashboard/work/service-jobs/{job}/` |

**Endpoints:**

```
GET    kanban-state                    → dashboard.work.kanban.show
POST   kanban-state/refresh            → dashboard.work.kanban.refresh
POST   blockers                        → dashboard.work.kanban.blockers.add
DELETE blockers/{blocker}              → dashboard.work.kanban.blockers.clear
GET    dispatch-priority               → dashboard.work.kanban.dispatch-priority
```

---

### Events

| File | Fired when |
|------|-----------|
| `app/Events/Work/JobKanbanStateChanged.php` | kanban_state transitions (normal↔blocked↔ready) |
| `app/Events/Work/JobBlockerAdded.php` | blocker attached to job |
| `app/Events/Work/JobBlockerCleared.php` | blocker resolved |

---

### Listeners

| File | Trigger | Action |
|------|---------|--------|
| `app/Listeners/Work/JobKanbanStateChangedListener.php` | `JobKanbanStateChanged` | Logs transition; extend for alerts |
| `app/Listeners/Work/JobStageChangedListener.php` | `JobStageChanged` | Extended to call `KanbanStatusService::refresh()` after billing/agreement pass |

---

### Model Extensions

#### ServiceJob

New fillable fields: `kanban_state`, `kanban_state_label`, `sla_deadline`, `sla_breached`, `readiness_score`

New relationships:
- `kanbanMeta()` → HasOne FsmJobStatusMeta
- `blockers()` → HasMany FsmJobBlocker
- `activeBlockers()` → HasMany FsmJobBlocker (unresolved only)
- `priorityScore()` → HasOne FsmJobPriorityScore

New computed attributes (Eloquent accessors):
- `is_ready_to_start`
- `is_waiting_parts`
- `is_blocked`
- `is_overdue`
- `requires_followup`
- `customer_action_pending`
- `dispatch_metadata`

Extended methods:
- `boardSummary()` — kanban state + readiness flags added
- `calendarMeta()` — kanban_state, is_ready_to_start, is_blocked, is_overdue, sla_breached added

#### JobStage

New fillable: `display_badge`, `badge_color`, `kanban_fold`
New cast: `kanban_fold` → boolean

---

## Source-to-Host Mapping

| Odoo fieldservice_kanban_info concept | Titan Zero mapping |
|--------------------------------------|---------------------|
| `fsm.order` kanban_state | `service_jobs.kanban_state` |
| Stage badges | `job_stages.display_badge` + `job_stages.badge_color` |
| Stage fold | `job_stages.kanban_fold` (extends existing `fold`) |
| Blocking reasons | `fsm_job_blockers` table + `FsmJobBlocker` model |
| Priority scoring | `fsm_job_priority_scores` + `KanbanStatusService` |
| SLA indicator | `service_jobs.sla_deadline` + `service_jobs.sla_breached` |
| Readiness overlay | `fsm_job_status_meta` (per-job computed flags) |

---

## Cross-Domain Compatibility

| Domain | Status |
|--------|--------|
| Projects / ServiceJob | ✅ Extended, not replaced |
| Service Agreements | ✅ agreement_expired flag computed via `$job->agreement` |
| CRM (Customer tier, VIP) | ✅ vip_client_flag + client_tier_score via customer relationship |
| Equipment / Warranty | ✅ equipment_warranty_expired, equipment_missing via warranty relationships |
| Routes / Dispatch | ✅ dispatch_metadata attribute; KanbanStatusService::getDispatchPriority() |
| Calendar | ✅ calendarMeta() extended with readiness signals |
| Recurring services | ✅ agreement-awareness included in scoring |
| EasyDispatch signals | ✅ priority_score + score_breakdown exposed via API + getDispatchPriority() |

---

## Deferred / Not Done

- **UI layouts**: No view files modified (intentional — issue requires metadata only)
- **EasyDispatch direct wiring**: No EasyDispatch service exists yet; metadata is exposed via API for future wiring
- **travel_conflict_flag**: Set to false by default; RouteService can write to `fsm_job_status_meta.travel_conflict_flag` once route conflict detection is implemented
- **Artisan refresh command**: A batch-refresh command for seeding kanban state on existing jobs can be added as a follow-up

---

## Module Count

FSM modules completed: **23 / 30**

Remaining: sale, sale_agreement, sale_agreement_equipment_stock, sale_recurring, route, route_availability, activity, portal, project_extensions
