# FSM Modules 9+10 — Repair Domain + Repair Template Engine

**Status:** Complete  
**Date:** 2026-04-03  
**Modules:** FSM-09 (fieldservice_repair) + FSM-10 (fieldservice_repair_order_template)

---

## Summary

Modules 9 and 10 introduce a canonical repair lifecycle domain with a template engine
to TitanZero. The implementation is additive — no existing host infrastructure was replaced.

---

## Files Delivered

### Migration
| File | Description |
|------|-------------|
| `database/migrations/2026_04_03_400000_create_repair_domain_tables.php` | Creates 11 repair domain tables |

### Models — `app/Models/Repair/`
| Model | Table | Description |
|-------|-------|-------------|
| `RepairOrder` | `repair_orders` | Central repair lifecycle aggregate; implements SchedulableEntity |
| `RepairDiagnosis` | `repair_diagnoses` | Symptom, cause, recommended action, flags |
| `RepairTask` | `repair_tasks` | Individual work items |
| `RepairAction` | `repair_actions` | Timestamped action log |
| `RepairPartUsage` | `repair_part_usages` | Parts reserved/consumed |
| `RepairChecklist` | `repair_checklists` | Checklist execution context |
| `RepairResolution` | `repair_resolutions` | Structured resolution record |
| `RepairTemplate` | `repair_templates` | Reusable repair procedure template |
| `RepairTemplateStep` | `repair_template_steps` | Ordered step in a template |
| `RepairTemplatePart` | `repair_template_parts` | Part specification in a template |
| `RepairTemplateChecklist` | `repair_template_checklists` | Checklist specification in a template |

### Events — `app/Events/Repair/`
32 events covering: order lifecycle, warranty, diagnosis, parts, template, premises, service, CRM, finance, scheduling.

### Listeners — `app/Listeners/Repair/`
| Listener | Trigger | Action |
|----------|---------|--------|
| `RepairOrderCreatedListener` | `RepairOrderCreated` | Emits premises/warranty/CRM secondary signals |
| `RepairOrderCompletedListener` | `RepairOrderCompleted` | Emits invoice/CRM upsell signals |
| `RepairWarrantyDetectedListener` | `RepairWarrantyDetected` | Emits claim-linked when claim is attached |
| `RepairDiagnosisRecordedListener` | `RepairDiagnosisRecorded` | Emits specialist/quote/parts signals |
| `RepairTemplateAppliedListener` | `RepairTemplateApplied` | Emits checklist/parts generated signals |

### Services — `app/Services/Repair/`
| Service | Description |
|---------|-------------|
| `RepairTemplateService` | Instantiates templates into RepairOrders; generates tasks, parts, checklists |

### Controllers — `app/Http/Controllers/Core/Repair/`
| Controller | Routes |
|------------|--------|
| `RepairOrderController` | CRUD + diagnosis + template apply + complete actions |
| `RepairTemplateController` | Full resource CRUD |

### Routes
`routes/core/repair.routes.php` — auto-loaded by RouteServiceProvider

### Views — `resources/views/core/repair/`
Index, create, edit, show, form partial for RepairOrder.
Index, create, edit, show, form partial for RepairTemplate.

---

## Host Model Extensions

### `ServiceJob` → `repairOrders()` (HasMany)
Repair orders originating from a service job are accessible via:
```php
$job->repairOrders(); // HasMany(RepairOrder, service_job_id)
```

### `RepairOrder` → `originatingServiceJob()` / `followupServiceJob()`
Bidirectional linkage from repair back to service job.

### `Premises` → repair intelligence methods
```php
$premises->repairOrders();       // HasMany
$premises->openRepairs();        // Collection of non-terminal repairs
$premises->repairHistory();      // Collection of completed repairs
$premises->repairRiskSummary();  // ['urgent'=>N, 'high'=>N, 'normal'=>N, 'low'=>N, 'total'=>N]
```

### `SchedulingSurfaceProvider` extended
RepairOrder added to `ENTITY_TYPES` and all surface methods:
- `getEventsForRange()`
- `getEventsForUser()`
- `getEventsForPremises()`
- `getEventsForTeam()`

All surface methods now aggregate RepairOrders alongside ServiceJob, ServicePlanVisit, InspectionInstance, ChecklistRun.

---

## Repair Status Lifecycle

```
draft → diagnosed → awaiting_parts → scheduled → in_progress → paused
                                               ↓
                                    awaiting_approval → completed → verified → closed
                                               ↓
                                           cancelled
```

---

## Template → Repair Order Pipeline

1. `RepairTemplate::createRepairOrder(attributes)` calls `RepairTemplateService`
2. Service generates `RepairOrder` with `repair_number` (REP-YYYYMMDD-XXXXX)
3. Service creates `RepairTask` for each template step
4. Service creates `RepairPartUsage` for each template part
5. Service creates `RepairChecklist` for each template checklist
6. `RepairTemplateApplied` event dispatched → secondary signals for checklist/parts

---

## Overlap Map

See `docs/repair_domain_overlap_map.json` for full canonical owner model mapping,
duplicates detected, and future merge candidates.

---

## Next Module

Ready for: **FSM Module 11 — fieldservice_route** (route planning and availability)
