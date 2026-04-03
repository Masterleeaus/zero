# FSM Module 11 — fieldservice_route Merge Report

**Date:** 2026-04-03
**Branch:** copilot/integrate-fsm-fieldservice-route
**Status:** ✅ Complete

---

## Scope

Module 11 merges the `fieldservice_route` and `fieldservice_route_availability` Odoo/FSM modules
into the TitanZero host core.  It delivers:

- Dispatch route template management (named routes with technician, days, capacity)
- Day-route run management (concrete date-based route execution)
- Technician availability schedule management
- Route feasibility service
- Full event-listener wiring for route lifecycle signals

---

## Files Delivered

### Models (Pass 1 — committed in previous session)

| File | Purpose |
|------|---------|
| `app/Models/Route/DispatchRoute.php` | Named route template |
| `app/Models/Route/DispatchRouteStop.php` | Concrete day-route run |
| `app/Models/Route/DispatchRouteStopItem.php` | Individual stop on a day-run |
| `app/Models/Route/TechnicianAvailability.php` | Technician working schedule |
| `app/Models/Route/AvailabilityWindow.php` | Per-day availability overrides |
| `app/Models/Route/RouteBlackoutDay.php` | Blocked date |
| `app/Models/Route/RouteBlackoutGroup.php` | Named group of blackout days |

### Events (Pass 1)

| File | Trigger |
|------|---------|
| `app/Events/Route/RouteCreated.php` | New route template created |
| `app/Events/Route/RouteUpdated.php` | Route template edited |
| `app/Events/Route/RouteAssigned.php` | Day-route stop assigned to technician |
| `app/Events/Route/RouteCapacityExceeded.php` | Day-route stop capacity breached |
| `app/Events/Route/RouteConflictDetected.php` | Scheduling conflict detected |
| `app/Events/Route/RouteStopAdded.php` | Stop item added to day-route |
| `app/Events/Route/RouteStopCompleted.php` | Stop item marked complete |
| `app/Events/Route/RouteStopFailed.php` | Stop item marked failed |
| `app/Events/Route/RouteStopReordered.php` | Stop items reordered |
| `app/Events/Route/TechnicianAvailabilityCreated.php` | Availability schedule created |
| `app/Events/Route/TechnicianAvailabilityUpdated.php` | Availability schedule edited |

### Services (Pass 1)

| File | Purpose |
|------|---------|
| `app/Services/Route/RouteService.php` | Feasibility checks, stop assignment, availability summary |
| `app/Services/Dispatch/RouteBoardCardDTO.php` | Dispatch board card DTO for route stops |
| `app/Services/Dispatch/DispatchBoardEventAdapter.php` | Extended to include route stops |
| `app/Services/Scheduling/SchedulingSurfaceProvider.php` | Extended to include DispatchRouteStop |

### Migration (Pass 1)

| File | Tables |
|------|--------|
| `database/migrations/2026_04_03_400100_create_dispatch_route_tables.php` | `dispatch_routes`, `dispatch_route_stops`, `dispatch_route_stop_items`, `technician_availabilities`, `availability_windows`, `route_blackout_days`, `route_blackout_groups` |

### Controllers (Pass 2 — this session)

| File | Routes |
|------|--------|
| `app/Http/Controllers/Core/Route/DispatchRouteController.php` | CRUD for dispatch routes |
| `app/Http/Controllers/Core/Route/TechnicianAvailabilityController.php` | CRUD for availability schedules |

### Routes (Pass 2)

| File | Prefix |
|------|--------|
| `routes/core/route.routes.php` | `dashboard/work/routes/*` |

Named routes:
- `dashboard.work.routes.index`
- `dashboard.work.routes.create`
- `dashboard.work.routes.store`
- `dashboard.work.routes.show`
- `dashboard.work.routes.edit`
- `dashboard.work.routes.update`
- `dashboard.work.routes.destroy`
- `dashboard.work.routes.availability.index`
- `dashboard.work.routes.availability.create`
- `dashboard.work.routes.availability.store`
- `dashboard.work.routes.availability.edit`
- `dashboard.work.routes.availability.update`
- `dashboard.work.routes.availability.destroy`

### Listeners (Pass 2)

| File | Event |
|------|-------|
| `app/Listeners/Route/RouteAssignedListener.php` | `RouteAssigned` |
| `app/Listeners/Route/RouteStopCompletedListener.php` | `RouteStopCompleted` |
| `app/Listeners/Route/RouteCapacityExceededListener.php` | `RouteCapacityExceeded` |

### Event Registrations (Pass 2)

`app/Providers/EventServiceProvider.php` updated:
- All 11 Route events registered in `$listen` array
- 3 active listeners wired (RouteAssigned, RouteStopCompleted, RouteCapacityExceeded)
- Remaining 8 events registered as stubs for future automation

### Views (Pass 2)

| File | Purpose |
|------|---------|
| `resources/views/default/panel/user/work/routes/index.blade.php` | Route list with filter |
| `resources/views/default/panel/user/work/routes/form.blade.php` | Create/edit route form |
| `resources/views/default/panel/user/work/routes/show.blade.php` | Route detail + recent runs |
| `resources/views/default/panel/user/work/routes/availability/index.blade.php` | Availability schedule list |
| `resources/views/default/panel/user/work/routes/availability/form.blade.php` | Create/edit availability form |

---

## Integration Notes

### Scheduling Surface
`SchedulingSurfaceProvider::ENTITY_TYPES` now includes `DispatchRouteStop`.
Route stops are aggregated alongside `ServiceJob`, `ServicePlanVisit`,
`InspectionInstance`, and `ChecklistRun`.

### Dispatch Board Adapter
`DispatchBoardEventAdapter` now emits `RouteBoardCardDTO` entries for active
day-route stops alongside existing work cards.

### Host Bridges
- `DispatchRoute` uses `BelongsToCompany` trait — multi-tenant safe
- `assigned_user_id` column aligns with host convention (matches `ServiceJob`)
- Territory/ServiceArea FK columns nullable — no hard dependency if those tables
  are not yet populated

---

## Deferred Work

- Route blackout group UI (admin only — deferred to a dedicated pass)
- Availability window per-day overrides UI
- Travel-time feasibility in `RouteService::travelWindowFeasible()`
- Mobile technician route-run view (PWA layer)
- Notification jobs for route lifecycle events

---

## Validation Summary

- ✅ All PHP files pass `php -l` syntax check
- ✅ Routes auto-loaded by `RouteServiceProvider` (regex allows `route.routes.php`)
- ✅ EventServiceProvider updated — no duplicate registrations
- ✅ Models use `BelongsToCompany` trait — tenancy consistent with host
- ✅ No duplicate host infrastructure introduced
- ✅ Next module in sequence: Module 12
