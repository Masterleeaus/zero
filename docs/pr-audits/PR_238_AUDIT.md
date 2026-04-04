# PR #238 — FSM Graph Verification + Drift Repair

**Status:** MERGED (Audit Pass 2 — 2026-04-04)  
**Risk Level:** Medium-Low  
**Domain:** FSM / Work / ORM Layer

## 1. Purpose

Full cross-module audit of the FSM execution graph. Repaired broken ORM inverse linkages
(FKs existed in DB but no Eloquent methods declared), registered 28 silently-dispatched
events that had no `$listen` entries, and created the missing `RepairOrderService`.

## 2. Scope

| Area | Files |
|---|---|
| Model ORM repairs | `ServiceJob`, `ServiceAgreement`, `ServicePlan`, `ServicePlanVisit`, `Premises`, `Vehicle`, `DispatchRoute` |
| New service | `app/Services/Repair/RepairOrderService.php` |
| EventServiceProvider | 29 new `use` imports + 28+ `$listen` entries |
| WorkCoreServiceProvider | `Relation::morphMap()` registration |
| FSM reports | 7 JSON report files + 2 Markdown docs |

## 3. Structural Fit

✅ ORM repairs are purely additive (new inverse relationship methods)  
✅ MorphMap registration resolves `VehicleAssignment` alias resolution correctly  
✅ `RepairOrderService` follows existing service patterns (Repair namespace)  
✅ All 28 events already existed as PHP classes — just unregistered in EventServiceProvider  
✅ Consistent with Titan convention: events must be explicitly registered

## 4. Code Quality

| Aspect | Assessment |
|---|---|
| ORM repairs | Correct inverse relationships with proper FK references |
| MorphMap | Necessary fix for `VehicleAssignment` polymorphism |
| RepairOrderService | Complete lifecycle: create/schedule/reserveParts/consumeParts/complete/close/cancel |
| Event stubs | All registered with `[]` listeners — ready for downstream wiring |

## 5. Conflict Review

No git conflicts. PR changes are purely additive:
- New methods appended to existing model files
- New entries added to EventServiceProvider
- New boot() code in WorkCoreServiceProvider

**Semantic note:** `ServicePlanVisit` and `ServiceJob` are also modified by PR #239.
Both sets of additions are non-overlapping and were merged cleanly.

**Premises.php:** PR #238 adds `serviceAgreements()` HasMany.
PR #240 changes `InspectionInstance` namespace. Both changes combined without conflict.

## 6. Merge Decision

**MERGED** — All changes additive. No conflicts with current main or other PRs.

## 7. Gap Analysis

| Gap | Severity | Next Pass |
|---|---|---|
| `RepairOrder ↔ VehicleStock` direct FK (needs new column on `repair_part_usages`) | Medium | Repair domain pass |
| `ServiceAgreement.$guarded = []` → explicit `$fillable` | Low | Hardening pass |
| Dispatch readiness score consolidation to single aggregator | Low | Dispatch intelligence pass |
| `ServiceJobSchedulingService`, `TechnicianAvailabilityService` | Low | Dispatch intelligence pass |
| 28 registered events have empty listeners — need wiring | Medium | Domain-specific listener passes |
