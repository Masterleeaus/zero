# Easy Dispatch Canonical Merge Report

**Date:** 2026-04-03
**Module:** Easy Dispatch Extraction + Canonical Merge
**Author:** Copilot Agent
**Status:** Complete

---

## Summary

This report documents the extraction of Easy Dispatch capabilities and their
canonical merge into the TitanZero dispatch graph. No standalone Easy Dispatch
subsystem has been created. All new logic is integrated into the existing
canonical service graph.

---

## Source Inventory (Stage A)

### Easy Dispatch Reference Implementation

Located at: `CodeToUse/Dispatch/easydispatch-main/`

The Python reference codebase provides the following dispatch intelligence
patterns that have been adapted and canonicalised:

| Easy Dispatch Capability            | Canonical Target                          | Status        |
|-------------------------------------|-------------------------------------------|---------------|
| Real-time job-to-worker assignment  | `DispatchService::allocate()`             | Existing + extended |
| Constraint-based scoring            | `DispatchConstraintService`               | Extended      |
| Technician availability evaluation  | `DispatchConstraintService::evaluateAvailability()` | New |
| Readiness snapshot                  | `DispatchReadinessService::dispatchReadiness()` | New |
| Dispatch blockers                   | `DispatchReadinessService::dispatchBlockers()` | New |
| Priority scoring                    | `DispatchReadinessService::dispatchPriorityScore()` | New |
| ETA context                         | `DispatchReadinessService::dispatchETAContext()` | New |
| Capacity fit                        | `DispatchReadinessService::dispatchCapacityFit()` | New |
| Conflict detection                  | `DispatchReadinessService::dispatchConflictReasons()` | New |
| Vehicle compatibility               | `VehicleDispatchService::vehicleDispatchReady()` | New |
| Vehicle route compatibility         | `VehicleDispatchService::vehicleRouteCompatibility()` | New |
| Vehicle capacity scoring            | `VehicleDispatchService::vehicleCapacityScore()` | New |
| Vehicle location fit                | `VehicleDispatchService::vehicleLocationFit()` | New |
| Stock readiness                     | `StockDispatchService::dispatchStockReady()` | New |
| Material risk                       | `StockDispatchService::dispatchMaterialRisk()` | New |
| Restock required                    | `StockDispatchService::dispatchRestockRequired()` | New |
| Parts blockers                      | `StockDispatchService::dispatchPartsBlockers()` | New |
| Agreement coverage eligibility      | `AgreementDispatchService::dispatchCoverageEligible()` | New |
| Warranty eligibility                | `AgreementDispatchService::dispatchWarrantyEligible()` | New |
| Repair blockers                     | `AgreementDispatchService::dispatchRepairBlocked()` | New |
| Sale commitment priority            | `AgreementDispatchService::dispatchSaleCommitmentPriority()` | New |
| Project context                     | `AgreementDispatchService::dispatchProjectContext()` | New |
| Job late detection                  | `DispatchJobLate` event                   | New event     |
| Readiness changed signal            | `DispatchReadinessChanged` event          | New event     |
| ETA changed signal                  | `DispatchETAChanged` event                | New event     |
| Vehicle blocked signal              | `DispatchVehicleBlocked` event            | New event     |
| Stock blocked signal                | `DispatchStockBlocked` event              | New event     |

---

## What Was Reused

### Existing canonical infrastructure retained without modification:

- `app/Services/Work/DispatchService.php` — core orchestration engine
- `app/Services/Work/DispatchConstraintService.php` — skill, territory, SLA evaluation
- `app/Models/Route/DispatchRoute.php` — named route templates
- `app/Models/Route/DispatchRouteStop.php` — concrete date-run records
- `app/Models/Route/DispatchRouteStopItem.php` — polymorphic stop items
- `app/Models/Work/DispatchAssignment.php` — assignment history
- `app/Models/Work/DispatchQueue.php` — priority queue
- `app/Models/Work/DispatchConstraint.php` — company constraint definitions
- `app/Models/Route/TechnicianAvailability.php` — working schedules
- `app/Models/Route/AvailabilityWindow.php` — per-day availability blocks
- `app/Models/Route/RouteBlackoutDay.php` — blocked service dates
- `app/Events/Work/JobDispatched.php`
- `app/Events/Work/JobDispatchFailed.php`
- `app/Events/Work/JobReDispatched.php`
- `app/Events/Route/RouteConflictDetected.php`
- `app/Events/Route/RouteCapacityExceeded.php`
- `app/Services/FSM/VehicleService.php` — vehicle assignment and stock management
- `app/Services/FSM/KanbanStatusService.php` — kanban priority intelligence
- All existing route event vocabulary

---

## What Was Extended

### `app/Services/Work/DispatchService.php`
- Added `DispatchReadinessService` injection via constructor
- Added `checkReadiness(ServiceJob): array` public method
- `checkReadiness()` emits `DispatchReadinessChanged`, `DispatchVehicleBlocked`,
  and `DispatchStockBlocked` events based on blocker detection results

### `app/Services/Work/DispatchConstraintService.php`
- Added `evaluateAvailability(User, ServiceJob): float` method
- Uses `TechnicianAvailability` bitmask to evaluate schedule fit for the job date
- Returns 1.0 (available), 0.5 (unknown/no schedule), or 0.0 (unavailable)

---

## What Was Added (New Canonical Files)

### Services

| File | Purpose |
|------|---------|
| `app/Services/Work/DispatchReadinessService.php` | Stage C — readiness engine |
| `app/Services/Work/VehicleDispatchService.php` | Stage E — vehicle-aware dispatch |
| `app/Services/Work/StockDispatchService.php` | Stage F — stock-aware dispatch |
| `app/Services/Work/AgreementDispatchService.php` | Stage G — agreement/warranty/repair awareness |

### Events

| File | Purpose |
|------|---------|
| `app/Events/Work/DispatchJobLate.php` | Job lateness signal |
| `app/Events/Work/DispatchReadinessChanged.php` | Readiness state change signal |
| `app/Events/Work/DispatchETAChanged.php` | ETA update signal |
| `app/Events/Work/DispatchVehicleBlocked.php` | Vehicle-level dispatch blocker |
| `app/Events/Work/DispatchStockBlocked.php` | Stock-level dispatch blocker |

### Tests

| File | Purpose |
|------|---------|
| `tests/Feature/Dispatch/EasyDispatchCanonicalMergeTest.php` | Feature test coverage for all new services |

---

## What Was Intentionally Not Merged

### Easy Dispatch Python Infrastructure
The Python-based scheduling engine in `CodeToUse/Dispatch/easydispatch-main/`
includes:
- OR-Tools Vehicle Routing Problem (VRP) solver
- Kafka-based real-time dispatch messaging
- Redis caching layer
- Reinforcement learning dispatch agent
- Drag-and-drop what-if analysis tools

These capabilities are **not yet merged** because:
1. The PHP canonical graph does not yet have a direct OR-Tools binding
2. Kafka/Redis infrastructure is present in TitanSignals but dispatch-specific
   broker topics are not yet defined
3. RL-based dispatch requires training data not yet available in the canonical DB

These remain as future dispatch intelligence work (see below).

### Easy Dispatch Standalone Models
The Python codebase defines `Job`, `Worker`, `Service`, `Team` entities.
These have **not** been preserved as separate PHP models because canonical
equivalents already exist:
- `Job` → `ServiceJob`
- `Worker` → `User` (technician)
- `Service` → `ServiceJob` + `ServicePlanVisit`
- `Team` → `Team` + `Shift`

---

## Dispatch Graph — Current Canonical State

```
Customer
  → Premises
    → ServiceJob / ServicePlanVisit / InspectionInstance / ChecklistRun
      → DispatchAssignment (technician, constraint_score)
      → DispatchQueue (priority_score)
      → DispatchRoute → DispatchRouteStop → DispatchRouteStopItem
      → Vehicle (via VehicleAssignment)
      → ServiceAgreement (coverage context)
      → WarrantyClaim (warranty context)
      → RepairOrder (repair blocker context)
      → FieldServiceProject (project grouping)
      → FsmJobBlocker (kanban blockers)
      → FsmJobPriorityScore (kanban priority)
```

---

## Remaining Future Dispatch Intelligence Work

- **VRP / OR-Tools integration**: batch route optimisation using stop sequencing
- **Kafka dispatch broker**: real-time job assignment via message bus
- **RL dispatch agent**: reinforcement-learning-based technician ranking
- **Manual resequencing UI**: drag-and-drop stop reordering on dispatch board
- **Dispatcher notes surface**: per-job/route dispatch commentary
- **PWA offline dispatch queue**: local resequencing with reconnect reconciliation
- **Multi-day route planning**: recurring shift-based route templates
- **ETA precision**: travel-time modelling from vehicle GPS vs. premises coordinates
- **Crew workload balancing**: capacity-weighted multi-technician scoring

---

## Integration Map

| New Service | Connects To |
|-------------|-------------|
| `DispatchReadinessService` | `DispatchConstraintService`, `VehicleDispatchService`, `StockDispatchService`, `AgreementDispatchService` |
| `VehicleDispatchService` | `Vehicle`, `VehicleAssignment`, `VehicleLocationSnapshot`, `ServiceJob` |
| `StockDispatchService` | `VehicleStock`, `ServiceJob`, `WarrantyClaim`, `RepairOrder` |
| `AgreementDispatchService` | `ServiceAgreement`, `WarrantyClaim`, `RepairOrder`, `FieldServiceProject` |
| `DispatchService::checkReadiness()` | `DispatchReadinessService`, dispatch event bus |

---

## Events Vocabulary (Canonical)

| Event | Trigger |
|-------|---------|
| `JobDispatched` | Job successfully allocated to technician |
| `JobDispatchFailed` | No eligible technician found |
| `JobReDispatched` | Job reallocated (reassignment) |
| `DispatchReadinessChanged` | Job readiness state changes (ready ↔ blocked) |
| `DispatchJobLate` | Job has passed its scheduled window |
| `DispatchETAChanged` | Travel estimate updated for assignment |
| `DispatchVehicleBlocked` | Vehicle prevents dispatch |
| `DispatchStockBlocked` | Stock shortage prevents dispatch |
| `RouteConflictDetected` | Route-level conflict (existing event, reused) |
| `RouteCapacityExceeded` | Route capacity breached (existing event, reused) |

---

## Success Checklist

- [x] Single dispatch board entity — `DispatchService`
- [x] Single scheduler — `DispatchService::allocate()` + `DispatchQueue`
- [x] Single route graph — `DispatchRoute` / `DispatchRouteStop` / `DispatchRouteStopItem`
- [x] Single readiness engine — `DispatchReadinessService`
- [x] Vehicle-aware dispatch — `VehicleDispatchService`
- [x] Stock-aware dispatch — `StockDispatchService`
- [x] Agreement-aware dispatch — `AgreementDispatchService`
- [x] Repair/warranty-aware dispatch — `AgreementDispatchService`
- [x] Kanban priority feeds dispatch — `dispatchPriorityScore()` uses `fsmPriorityScore`
- [ ] PWA-compatible dispatch updates — deferred (offline queue not yet merged)
- [x] No standalone Easy Dispatch subsystem

---

_Report generated by Copilot Agent. See `tests/Feature/Dispatch/EasyDispatchCanonicalMergeTest.php` for validation coverage._
