# FSM Modules 24 + 25 — fieldservice_vehicle + fieldservice_vehicle_stock
## Implementation Report

**Date:** 2026-04-03
**Modules:** fieldservice_vehicle (Module 24) + fieldservice_vehicle_stock (Module 25)
**Status:** ✅ Merged into zero-main

---

## What Was Built

### Stage A — Vehicle Domain Models
| File | Description |
|------|-------------|
| `app/Models/Vehicle/Vehicle.php` | Canonical crew vehicle — type, team, driver, capacity, capability tags, status |
| `app/Models/Vehicle/VehicleAssignment.php` | Polymorphic assignment to ServiceJob / DispatchRoute / Shift |
| `app/Models/Vehicle/VehicleStock.php` | Stock line items onboard a vehicle with reservation/consumption lifecycle |
| `app/Models/Vehicle/VehicleEquipment.php` | Equipment items loaded on a vehicle (bridges Equipment domain) |
| `app/Models/Vehicle/VehicleLocationSnapshot.php` | Lightweight lat/lng point-in-time record (not full telematics) |
| `database/factories/Vehicle/VehicleFactory.php` | Test factory |

### Stage B — Existing Model Extensions
| Model | Change |
|-------|--------|
| `app/Models/Work/Shift.php` | Added `vehicle_id` FK + `vehicle()` relation |
| `app/Models/Route/DispatchRoute.php` | Added `vehicle_id` FK + `vehicle()` relation |
| `app/Models/Work/ServiceJob.php` | Added `assigned_vehicle_id`, `required_vehicle_type`, and vehicle helper methods |

### Stage C — Vehicle Stock Layer
VehicleStock supports:
- `available` → `reserved` → `consumed` lifecycle
- `reserveForJob()` / `consume()` helpers
- `available_quantity` computed attribute

### Stage D — ServiceJob Helpers
New methods on `ServiceJob`:
- `assignedVehicle()` — BelongsTo relation
- `vehicleCompatibilityStatus()` — checks capability tag match
- `vehicleStockAvailable()` — checks onboard available stock
- `vehicleEquipmentAvailable()` — checks equipment is loaded

### Stage E — VehicleService
`app/Services/FSM/VehicleService.php` provides:
- `assignVehicleToJob(Vehicle, ServiceJob)` → VehicleAssignment
- `releaseVehicleFromJob(ServiceJob)` → void
- `assignVehicleToRoute(Vehicle, DispatchRoute)` → VehicleAssignment
- `checkJobCompatibility(Vehicle, ServiceJob)` → readiness flags array
- `findCompatibleVehicles(ServiceJob)` → Collection<Vehicle>
- `reserveStockForJob(Vehicle, ServiceJob, items)` → void
- `consumeStockOnJob(Vehicle, ServiceJob, items)` → void
- `recordLocationSnapshot(Vehicle, coords)` → VehicleLocationSnapshot
- `getRouteReadinessStatus(Vehicle, ServiceJob)` → array

### Stage G — Location Snapshots
`VehicleLocationSnapshot` with sources: `mobile | gps | manual | system`

### Stage H — Events
| Event | Trigger |
|-------|---------|
| `VehicleAssignedToJob` | Vehicle assigned to service job |
| `VehicleStockReserved` | Stock item reserved for job |
| `VehicleStockConsumed` | Stock consumed on site |
| `VehicleEquipmentMissing` | Vehicle missing required capability |
| `VehicleRouteReady` | Vehicle passes compatibility check |
| `VehicleLocationUpdated` | New location snapshot recorded |

### Stage I — Routes + Controller
New routes under `dashboard.work.vehicles.*`:
- `GET /dashboard/work/vehicles` — index
- `GET /dashboard/work/vehicles/create` — create form
- `POST /dashboard/work/vehicles` — store
- `GET /dashboard/work/vehicles/{vehicle}` — show
- `GET /dashboard/work/vehicles/{vehicle}/edit` — edit
- `PUT /dashboard/work/vehicles/{vehicle}` — update
- `POST /dashboard/work/vehicles/{vehicle}/assign-job` — assign vehicle to job
- `POST /dashboard/work/vehicles/{vehicle}/location-snapshot` — record location
- `GET /dashboard/work/vehicles/{vehicle}/compatibility/{job}` — compatibility check

### Migration
`database/migrations/2026_04_03_500400_create_vehicle_domain_tables.php`:
- Creates: `vehicles`, `vehicle_assignments`, `vehicle_stock`, `vehicle_equipment`, `vehicle_location_snapshots`
- Extends: `shifts` (vehicle_id), `dispatch_routes` (vehicle_id), `service_jobs` (assigned_vehicle_id, required_vehicle_type)

---

## Overlap Maps
- `fieldservice_vehicle_overlap_map.json`
- `fieldservice_vehicle_stock_overlap_map.json`

---

## Files Changed
- `app/Models/Vehicle/Vehicle.php` ← NEW
- `app/Models/Vehicle/VehicleAssignment.php` ← NEW
- `app/Models/Vehicle/VehicleStock.php` ← NEW
- `app/Models/Vehicle/VehicleEquipment.php` ← NEW
- `app/Models/Vehicle/VehicleLocationSnapshot.php` ← NEW
- `app/Services/FSM/VehicleService.php` ← NEW
- `app/Http/Controllers/Core/Work/VehicleController.php` ← NEW
- `app/Events/Work/VehicleAssignedToJob.php` ← NEW
- `app/Events/Work/VehicleStockReserved.php` ← NEW
- `app/Events/Work/VehicleStockConsumed.php` ← NEW
- `app/Events/Work/VehicleEquipmentMissing.php` ← NEW
- `app/Events/Work/VehicleRouteReady.php` ← NEW
- `app/Events/Work/VehicleLocationUpdated.php` ← NEW
- `database/migrations/2026_04_03_500400_create_vehicle_domain_tables.php` ← NEW
- `database/factories/Vehicle/VehicleFactory.php` ← NEW
- `resources/views/default/panel/user/work/vehicles/index.blade.php` ← NEW
- `resources/views/default/panel/user/work/vehicles/create.blade.php` ← NEW
- `resources/views/default/panel/user/work/vehicles/show.blade.php` ← NEW
- `resources/views/default/panel/user/work/vehicles/edit.blade.php` ← NEW
- `tests/Feature/FSM/VehicleDomainTest.php` ← NEW
- `app/Models/Work/ServiceJob.php` ← EXTENDED (vehicle helpers + pre-existing syntax fix)
- `app/Models/Work/Shift.php` ← EXTENDED (vehicle_id)
- `app/Models/Route/DispatchRoute.php` ← EXTENDED (vehicle_id)
- `routes/core/work.routes.php` ← EXTENDED (vehicle routes)

---

## No Duplicates Created
- Team logic: reused via FK
- Equipment domain: bridged via FK, no new equipment subsystem
- Inventory/Stock: VehicleStock is vehicle-scoped; no conflict with warehouse inventory
- Dispatch/Route: extended existing DispatchRoute, not replaced
