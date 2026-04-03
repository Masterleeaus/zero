# MODULE_01 — TitanDispatch

**Status:** Installed  
**Installed At:** 2026-04-03  
**Domain:** Work / Field Service Management

---

## Summary

TitanDispatch is the AI-driven technician assignment engine for Titan Zero. It evaluates candidate technicians against weighted constraints (skill match, territory coverage, SLA urgency) and automatically assigns the best-fit technician to a service job.

---

## Components Implemented

### Migration
- `database/migrations/2026_04_03_800100_create_dispatch_tables.php`
  - `dispatch_assignments` — records each technician assignment with constraint score, status lifecycle (pending → confirmed/declined/superseded), and soft deletes
  - `dispatch_constraints` — company-scoped weighted constraint definitions (skill, territory, availability, sla, travel_cost)
  - `dispatch_queue` — priority queue for jobs awaiting auto-dispatch

### Models
- `app/Models/Work/DispatchAssignment.php` — assignment record with `job()` and `technician()` relationships
- `app/Models/Work/DispatchConstraint.php` — constraint definition with `scopeActive()`
- `app/Models/Work/DispatchQueue.php` — queue entry with `job()` relationship

### Services
- `app/Services/Work/DispatchConstraintService.php` — evaluates skill match, territory match, and SLA urgency per candidate
- `app/Services/Work/DispatchService.php` — orchestrates allocation, scoring, queuing, confirmation, and re-dispatch; integrates SignalDispatcher and AuditTrail

### Events
- `app/Events/Work/JobDispatched.php`
- `app/Events/Work/JobDispatchFailed.php`
- `app/Events/Work/JobReDispatched.php`

### Listeners
- `app/Listeners/Work/RecordDispatchAuditTrail.php` — writes audit entry on successful dispatch
- `app/Listeners/Work/NotifyTechnicianOfAssignment.php` — stubbed for Phase 2 comms integration

### Controller
- `app/Http/Controllers/Core/Work/DispatchController.php`

### Routes (added to `routes/core/work.routes.php`)
| Method | URI | Name |
|--------|-----|------|
| GET | `/dashboard/work/dispatch` | `dashboard.work.dispatch.index` |
| POST | `/dashboard/work/dispatch/assign` | `dashboard.work.dispatch.assign` |
| POST | `/dashboard/work/dispatch/auto` | `dashboard.work.dispatch.auto` |
| GET | `/dashboard/work/dispatch/history` | `dashboard.work.dispatch.history` |

---

## Integration Points

| Connects To | Detail |
|-------------|--------|
| `ServiceJob` | Reads `company_id`, `assigned_user_id`, `job_type_id`, `premises_id`, `sla_deadline` |
| `User` | Candidate pool scoped by `company_id` |
| `SignalDispatcher` | Emits `dispatch.allocated`, `dispatch.failed`, `dispatch.confirmed`, `dispatch.reassigned` |
| `AuditTrail` | Records all dispatch events to `tz_audit_log` |
| `EventServiceProvider` | `JobDispatched`, `JobDispatchFailed`, `JobReDispatched` registered |

---

## Deferred / Phase 2
- Technician notification via comms layer (`NotifyTechnicianOfAssignment`)
- Travel time estimation (Google Maps / routing engine integration)
- Availability window constraint evaluation
- Manual override endpoint with audit trail
