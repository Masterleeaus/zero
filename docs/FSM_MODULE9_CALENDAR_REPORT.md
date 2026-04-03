# FSM Module 9 Merge Report — fieldservice_calendar

## Overview

Module 9 integrates the `fieldservice_calendar` Odoo module into the canonical
TitanZero/WorkCore architecture. All calendar behavior routes through the
existing unified scheduling surface rather than a parallel calendar system.

No new calendar-only job model was introduced. All scheduling logic flows
through the canonical SchedulingSurfaceProvider → CalendarEventDTO pipeline.

---

## Stage A — Connectable Domain Audit

| Domain | Status | Notes |
|--------|--------|-------|
| ServiceJob | ✅ Already linked | Has `toCalendarEvent()`, `calendarTitle()`, `calendarColor()`, `calendarMeta()` (enhanced this pass) |
| ServicePlanVisit | ✅ Newly complete | Added `toCalendarEvent()`, `calendarTitle()`, `calendarMeta()` |
| InspectionInstance | ✅ Newly complete | Added `toCalendarEvent()`, `calendarTitle()`, `calendarColor()`, `calendarMeta()` |
| ChecklistRun | ✅ Newly complete | Added `toCalendarEvent()`, `calendarTitle()`, `calendarMeta()` |
| Customer | ✅ Mostly linked; extended | Added `upcomingInspections()` to complement existing `upcomingServiceVisits()` |
| Premises | ✅ Mostly linked; extended | Added `upcomingInspections()`, `upcomingServiceJobs()` |
| Team | ✅ Newly complete | `getEventsForTeam()` added to SchedulingSurfaceProvider; `getCalendarEventsForTeam()` added to BusinessSuiteCalendarAdapter |
| Enquiry / Deal | ✅ Already linked via ServiceJob | ServiceJob carries enquiry_id / deal_id; calendarMeta() exposes them |
| ServiceAgreement | ✅ Already linked via ServiceJob | ServiceJob carries agreement_id; calendarMeta() exposes it |
| Equipment / InstalledEquipment | ✅ Already linked via ServiceJob and Premises | No calendar-specific fields needed; entity is a filter target |
| SiteAsset | ✅ Accessible via Premises | No direct calendar link needed; InspectionInstance scope covers asset inspections |
| Hazard | ✅ Already surfaced | Premises.timeline() + activeHazards(); calendarMeta() in InspectionInstance |
| SiteAccessProfile | ✅ Already surfaced | Premises.activeSiteAccess(); calendarMeta() in ServiceJob via siteHazards() |
| Meter / MeterReading | ⏭ Deferred | No direct scheduling surface; not a calendar event type |
| SchedulingSurfaceProvider | ✅ Extended | Added `getEventsForTeam()`, public `normaliseEntity()` |
| BusinessSuiteCalendarAdapter | ✅ Extended | Added `getCalendarEventsForTeam()` |
| DispatchBoardEventAdapter | ✅ Unchanged | Already consumes SchedulingSurfaceProvider; compatible |

---

## Stage B — Source Module Behavior Extracted

The Odoo `fieldservice_calendar` module provides:
- Calendar event projections from `fsm.order` records
- Recurrence awareness via `calendar.event` linkage
- Technician assignment visibility on calendar

All of these behaviors are handled in TitanZero through:
- `SchedulingSurfaceProvider.getEventsForUser()` / `getEventsForTeam()`
- `toCalendarEvent()` methods on each schedulable model
- `BusinessSuiteCalendarAdapter` as the unified calendar projection layer

---

## Stage C — Unified Scheduling Surface

Reused existing SchedulingSurfaceProvider. Extended with:

| Addition | Location | Purpose |
|----------|----------|---------|
| `getEventsForTeam(int $teamId)` | SchedulingSurfaceProvider | Team workload calendar surface |
| `normaliseEntity(SchedulableEntity, ...)` | SchedulingSurfaceProvider | Public entity normalisation for listeners/services |

---

## Stage D — Cross-Domain Calendar Connections

| Connection | Implementation |
|-----------|---------------|
| ServiceJob → calendar | `toCalendarEvent()` with enriched meta including customer, team, premises, agreement, enquiry, deal |
| ServicePlanVisit → calendar | `toCalendarEvent()` added; includes plan_id, dispatched status, premises/customer via plan |
| InspectionInstance → calendar | `toCalendarEvent()` added; type-based colour coding; includes scope context |
| ChecklistRun → calendar | `toCalendarEvent()` added; completion percentage in meta |
| Customer → upcoming inspections | `upcomingInspections()` added |
| Premises → upcoming inspections | `upcomingInspections()` added |
| Premises → upcoming jobs | `upcomingServiceJobs()` added |
| Team/User → workload calendar | `getEventsForTeam()` in provider; `getCalendarEventsForTeam()` in adapter |

---

## Stage E — Calendar Helpers Added

### ServiceJob
| Helper | Notes |
|--------|-------|
| `calendarTitle()` | Base title + customer name suffix |
| `calendarColor()` | Stage colour → priority fallback (urgent=red, high=orange, normal=blue, low=gray) |
| `calendarMeta()` | type, status, priority, assignee, team, customer, premises, site, duration, is_billable, enquiry_id, deal_id, agreement_id |
| `toCalendarEvent()` | Now delegates to calendarTitle/calendarColor/calendarMeta |

### ServicePlanVisit
| Helper | Notes |
|--------|-------|
| `toCalendarEvent()` | Green (#22c55e); delegates to calendarTitle/calendarMeta |
| `calendarTitle()` | "[Visit] {plan name}" |
| `calendarMeta()` | type, status, visit_type, assignee_id, plan_id, service_job_id, is_dispatched, premises_id, customer_id |

### InspectionInstance
| Helper | Notes |
|--------|-------|
| `toCalendarEvent()` | Type-based colour; end = completed_at |
| `calendarTitle()` | "[Inspection] {title}" |
| `calendarColor()` | compliance=red, safety=orange, handover=violet, quality=sky, default=orange |
| `calendarMeta()` | type, inspection_type, status, assignee_id, service_job_id, schedule_id, followup_required, score, scope_type, scope_id |

### ChecklistRun
| Helper | Notes |
|--------|-------|
| `toCalendarEvent()` | Purple (#a855f7); start=started_at, end=completed_at |
| `calendarTitle()` | "[Checklist] {title}" |
| `calendarMeta()` | type, status, assignee_id, completion_pct, items_total, items_completed, items_failed, runnable_type, runnable_id |

---

## Stage F — Calendar Lifecycle Events

### New Events
| Event | Namespace | Payload |
|-------|-----------|---------|
| `ServiceJobScheduled` | `App\Events\Work` | ServiceJob |
| `ServiceJobRescheduled` | `App\Events\Work` | ServiceJob, previousStart |
| `ServiceJobUnscheduled` | `App\Events\Work` | ServiceJob, previousStart |
| `ServicePlanVisitRescheduled` | `App\Events\Work` | ServicePlanVisit, previousDate |
| `InspectionRescheduled` | `App\Events\Inspection` | InspectionInstance, previousScheduledAt |

### New Listeners
| Listener | Event | Behavior |
|---------|-------|---------|
| `ServiceJobScheduledListener` | ServiceJobScheduled | Broadcasts new event to BusinessSuiteCalendarAdapter |
| `ServiceJobRescheduledListener` | ServiceJobRescheduled | Removes stale key, broadcasts updated event |

All new events registered in `EventServiceProvider::$listen`.
Existing events reused: `ServicePlanVisitScheduled`, `InspectionScheduled`.

---

## Stage G — Calendar Adapter Layer

BusinessSuiteCalendarAdapter extended:
- `getCalendarEventsForTeam(int $teamId)` added

Existing adapters remain canonical:
- BusinessSuiteCalendarAdapter — Business Suite calendar surface
- DispatchBoardEventAdapter — EasyDispatch / dispatch board (no changes needed)

---

## Stage H — Cross-Domain Schedule Graph

Graph connections verified:

```
Customer
  → serviceJobs()           (ServiceJob)
  → upcomingServiceVisits() (ServicePlanVisit via ServicePlan)
  → upcomingInspections()   (InspectionInstance) ← NEW
  → premises()
      → upcomingServiceJobs()   ← NEW
      → serviceVisits()         (ServicePlanVisit via ServicePlan)
      → upcomingInspections()   ← NEW
      → inspections()           (InspectionInstance)
      → activeHazards()
      → activeSiteAccess()

Team
  → getEventsForTeam()          ← NEW (via SchedulingSurfaceProvider)
  → getCalendarEventsForTeam()  ← NEW (via BusinessSuiteCalendarAdapter)

ServiceJob
  → toCalendarEvent()           (enhanced)
  → calendarTitle()             ← NEW
  → calendarColor()             ← NEW
  → calendarMeta()              ← NEW

ServicePlanVisit
  → toCalendarEvent()           ← NEW
  → calendarTitle()             ← NEW
  → calendarMeta()              ← NEW

InspectionInstance
  → toCalendarEvent()           ← NEW
  → calendarTitle()             ← NEW
  → calendarColor()             ← NEW
  → calendarMeta()              ← NEW

ChecklistRun
  → toCalendarEvent()           ← NEW
  → calendarTitle()             ← NEW
  → calendarMeta()              ← NEW
```

---

## Stage I — FSM Module Status

| # | Module | Status |
|---|--------|--------|
| 1 | fieldservice_base | ✅ Merged |
| 2 | fieldservice_stock | ✅ Merged |
| 3 | fieldservice_sale | ✅ Merged |
| 4 | fieldservice_activity | ✅ Merged |
| 5 | fieldservice_kanban_info | ✅ Merged |
| 6 | fieldservice_crm | ✅ Merged |
| 7 | fieldservice_equipment | ✅ Merged |
| 8 | fieldservice_route | ✅ Merged |
| 9 | **fieldservice_calendar** | ✅ **Merged (this pass)** |

Next module in sequence: **Module 10**

---

## Intentionally Deferred

| Item | Reason |
|------|--------|
| Recurrence rule engine | Handled at ServicePlan/InspectionSchedule level; no separate calendar recurrence model needed |
| Meter/MeterReading calendar events | Not a scheduled event type; no calendar surface needed |
| Front-end calendar view (Blade/JS) | UI pass; backend surface complete and ready to consume |
| Equipment maintenance calendar events | Future enhancement; requires maintenance scheduling schema |
| EasyDispatch calendar feed | DispatchBoardEventAdapter already consumes SchedulingSurfaceProvider; no changes needed |
