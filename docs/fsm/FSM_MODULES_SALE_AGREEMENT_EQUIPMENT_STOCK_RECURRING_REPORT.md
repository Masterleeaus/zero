# FSM Modules: fieldservice_sale_agreement_equipment_stock + fieldservice_sale_recurring — Integration Report

**Date:** 2026-04-03
**Modules:** fieldservice_sale_agreement_equipment_stock, fieldservice_sale_recurring
**Integration target:** WorkCore Canonical Lifecycle Graph

---

## Summary

These two modules extend the canonical service lifecycle to cover:

1. **Agreement-sold equipment coverage** — equipment sold through an agreement is
   tracked as an `InstalledEquipment` instance with an active `ServiceAgreement`
   coverage record.

2. **Recurring sale engine** — recurring service products sold via a `Quote` generate
   a `ServicePlan`, which produces `ServicePlanVisit` records on a schedule, which
   materialize into `ServiceJob` execution records.

Both modules attach exclusively to existing canonical entities. No parallel FSM tables
were created.

---

## Deliverables

### Migration

| File | Purpose |
|------|---------|
| `database/migrations/2026_04_03_500500_add_fieldservice_sale_agreement_equipment_stock_recurring_columns.php` | Adds coverage + recurring columns across 4 tables |

**installed_equipment columns added:**

| Column | Type | Purpose |
|--------|------|---------|
| `agreement_id` | bigint nullable | Service agreement that covers this installation |
| `sale_quote_id` | bigint nullable | Quote through which equipment was sold |
| `coverage_start_date` | date nullable | Coverage activation date |
| `coverage_end_date` | date nullable | Coverage expiry date |
| `coverage_activated_at` | timestamp nullable | When coverage was first activated |

**service_plans columns added:**

| Column | Type | Purpose |
|--------|------|---------|
| `origin_quote_id` | bigint nullable | Quote that triggered recurring plan generation |
| `recurring_product_ref` | string nullable | Product / SKU of recurring service |
| `recurrence_type` | string nullable | maintenance \| inspection \| compliance \| contract |
| `auto_generate_visits` | boolean | Auto-create visits when plan advances |
| `equipment_scope` | json nullable | Array of InstalledEquipment IDs in scope |

**service_agreements columns added:**

| Column | Type | Purpose |
|--------|------|---------|
| `has_equipment_coverage` | boolean | Flag: agreement covers sold equipment |
| `recurring_plan_count` | smallint | Cached count of active recurring plans |

**service_plan_visits columns added:**

| Column | Type | Purpose |
|--------|------|---------|
| `installed_equipment_id` | bigint nullable | Equipment this visit specifically services |
| `coverage_source` | string nullable | agreement \| warranty \| manual |
| `recurring_sale_ref` | string nullable | Trace reference to originating recurring sale |

---

### Services

| File | Namespace | Purpose |
|------|-----------|---------|
| `app/Services/Work/EquipmentCoverageService.php` | `App\Services\Work` | Agreement coverage lifecycle for sold equipment |
| `app/Services/Work/RecurringSaleService.php` | `App\Services\Work` | Recurring sale → plan → visits → jobs pipeline |

**EquipmentCoverageService methods:**

| Method | Description |
|--------|-------------|
| `activateCoverageForEquipment()` | Activate agreement coverage on an InstalledEquipment instance |
| `activateCoverageFromEquipment()` | Activate coverage from a catalogue Equipment (creates IE if needed) |
| `extendCoverageForEquipment()` | Extend existing coverage on renewal |
| `coveredEquipmentForAgreement()` | All active IE records covered by an agreement |
| `activelyCoveredEquipment()` | IE records with non-expired coverage dates |
| `coverageTimeline()` | Structured coverage timeline with per-equipment visit stats |
| `createEquipmentRecurringPlan()` | Create a recurring ServicePlan for a covered equipment unit |
| `activateCoverageFromQuote()` | Bulk coverage activation from quote items |

**RecurringSaleService methods:**

| Method | Description |
|--------|-------------|
| `recurringLinesFromQuote()` | Detect recurring service lines on a Quote |
| `createRecurringPlanFromSale()` | Create a ServicePlan from an accepted Quote |
| `createPlansFromRecurringLines()` | One plan per distinct recurring type on a Quote |
| `generateVisitsForPlan()` | Generate ServicePlanVisit records for a date range |
| `materializeVisitToJob()` | Convert a pending visit into a ServiceJob |
| `materializeDueVisits()` | Batch materialize all due visits for a plan |
| `regeneratePlansFromAgreementUpdate()` | Regenerate visits on agreement update |
| `runRecurringPipeline()` | Full pipeline: quote → plans → visits |

---

### Events (7 new)

| File | Fires when |
|------|-----------|
| `app/Events/Work/AgreementEquipmentCoverageCreated.php` | Agreement coverage activated for an equipment instance |
| `app/Events/Work/AgreementEquipmentCoverageExtended.php` | Existing equipment coverage renewed / extended |
| `app/Events/Work/RecurringSaleCreated.php` | Recurring sale detected on an accepted Quote |
| `app/Events/Work/RecurringPlanGenerated.php` | ServicePlan generated from a recurring sale |
| `app/Events/Work/RecurringPlanUpdated.php` | Existing recurring plan updated / regenerated |
| `app/Events/Work/RecurringEquipmentServiceCreated.php` | Recurring plan created for a specific equipment unit |
| `app/Events/Work/RecurringVisitMaterialized.php` | Pending visit converted to a ServiceJob |

---

### Model Helpers Added

**ServiceAgreement**

| Method | Returns | Description |
|--------|---------|-------------|
| `installedEquipment()` | HasMany | All IE records covered by this agreement |
| `coveredEquipment()` | Collection | Active IE with non-expired coverage |
| `recurringCoverageSummary()` | array | Plan/visit/equipment coverage dimensions |
| `coverageTimeline()` | array | Per-equipment coverage state + visit counts |

**InstalledEquipment**

| Method | Returns | Description |
|--------|---------|-------------|
| `agreementCoverage()` | BelongsTo | Linked ServiceAgreement |
| `coverageOriginSale()` | BelongsTo | Originating Quote for coverage activation |
| `coverageVisits()` | HasMany | ServicePlanVisit records for this unit |
| `maintenanceSchedule()` | Collection | Upcoming pending/scheduled visits |
| `hasCoverageAgreement()` | bool | Whether active agreement coverage exists |

**ServicePlan**

| Method | Returns | Description |
|--------|---------|-------------|
| `originatingSale()` | Quote\|null | Quote that triggered this plan (via origin_quote_id or agreement) |
| `coverageEquipment()` | Collection | InstalledEquipment from equipment_scope |
| `recurringCoverageScope()` | array | Coverage scope summary |

**ServicePlanVisit**

| Method | Returns | Description |
|--------|---------|-------------|
| `installedEquipment()` | BelongsTo | Linked InstalledEquipment |
| `coverageSource()` | string | agreement \| warranty \| manual |
| `agreementOrigin()` | ServiceAgreement\|null | Agreement via plan |
| `equipmentContext()` | array\|null | Equipment context for this visit |

---

### Overlap Map

`fieldservice_sale_recurring_overlap_map.json` — documents already-integrated logic,
new deliverables, and duplicate-risk analysis.

---

## Lifecycle Pipeline

```
Quote (accepted, has recurring lines)
  ↓ RecurringSaleService.runRecurringPipeline()
  ↓ [RecurringSaleCreated event]
ServicePlan (origin_quote_id, recurrence_type, equipment_scope)
  ↓ [RecurringPlanGenerated event]
ServicePlanVisit[] (coverage_source=agreement, scheduled_date)
  ↓ RecurringSaleService.materializeVisitToJob()
  ↓ [RecurringVisitMaterialized event]
ServiceJob (agreement_id, premises_id, customer_id)
```

Equipment coverage pipeline:

```
Quote (accepted, has equipment items)
  ↓ EquipmentCoverageService.activateCoverageFromQuote()
InstalledEquipment (agreement_id, coverage_activated_at)
  ↓ [AgreementEquipmentCoverageCreated event]
ServicePlan (equipment_scope=[ie.id], recurrence_type=maintenance)
  ↓ [RecurringEquipmentServiceCreated event]
ServicePlanVisit (installed_equipment_id, coverage_source=agreement)
```

---

## Next Modules Ready

Per STAGE J:

- `fieldservice_repair`
- `fieldservice_repair_order_template`
