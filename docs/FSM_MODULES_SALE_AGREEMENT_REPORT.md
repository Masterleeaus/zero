# FSM Modules — fieldservice_sale + fieldservice_sale_agreement + fieldservice_sale_recurring

**Merge Date:** 2026-04-04  
**Author:** Copilot  
**Domain:** Work / CRM / Money  
**Migration:** 2026_04_03_500600_create_field_service_agreements_table

---

## Overview

This report documents the merge of three Odoo FSM modules into the Titan Work domain:

| Module | Odoo Equivalent | Status |
|--------|----------------|--------|
| `fieldservice_sale` | `fieldservice_sale` | Already merged (module_3) |
| `fieldservice_sale_agreement` | `fieldservice_sale_agreement` | **Merged this PR** |
| `fieldservice_sale_recurring` | `fieldservice_sale_recurring` | **Merged this PR** |

The merge creates a **contract-driven service lifecycle layer** linking:

```
Quote → FieldServiceAgreement → ServicePlanVisits → ServiceJobs → Invoices
```

---

## Model Additions

### New: `app/Models/Work/FieldServiceAgreement.php`

Table: `field_service_agreements`

| Column | Type | Purpose |
|--------|------|---------|
| `company_id` | bigint | Multi-tenant scope |
| `customer_id` | bigint | Linked customer |
| `premises_id` | bigint | Service location |
| `quote_id` | bigint | Originating quote |
| `title` | string | Display name |
| `reference` | string | Human-readable ref |
| `start_date` | date | Contract start |
| `end_date` | date | Contract end (nullable = open-ended) |
| `billing_cycle` | string | monthly/quarterly/annually/one_off |
| `service_frequency` | string | weekly/fortnightly/monthly/quarterly |
| `status` | string | draft/active/suspended/expired/cancelled/renewed |
| `terms_json` | json | Agreement terms |
| `auto_generate_jobs` | boolean | Auto-create jobs from visits |
| `auto_generate_visits` | boolean | Auto-project visit schedule |

**Relationships:**
- `customer()` → `Customer`
- `premises()` → `Premises`
- `quote()` → `Quote`
- `jobs()` → `ServiceJob` (via `recurring_source_id`)
- `visits()` → `ServicePlanVisit` (via `field_service_agreement_id`)
- `saleLines()` → `QuoteItem` (via `field_service_agreement_id`)

### Extended: `app/Models/Work/ServiceJob.php`

| Column Added | Purpose |
|-------------|---------|
| `contract_visit_id` | The ServicePlanVisit that spawned this job |
| `recurring_source_id` | The FieldServiceAgreement recurring source |

**New relationships:**
- `contractVisit()` → `ServicePlanVisit`
- `recurringSource()` → `FieldServiceAgreement`

### Extended: `app/Models/Work/ServicePlanVisit.php`

| Column Added | Purpose |
|-------------|---------|
| `field_service_agreement_id` | The FieldServiceAgreement driving this visit |
| `sale_line_id` | The QuoteItem (sale order line) that originated this visit |

**New relationships:**
- `fieldServiceAgreement()` → `FieldServiceAgreement`
- `saleLine()` → `QuoteItem`

### Extended: `app/Models/Crm/Customer.php`

| Method Added | Purpose |
|-------------|---------|
| `activeServiceContracts()` | All active FieldServiceAgreements for this customer |
| `expiringServiceContracts(int $withinDays)` | Contracts expiring within N days |

---

## Migration Changes

**File:** `database/migrations/2026_04_03_500600_create_field_service_agreements_table.php`

- Creates `field_service_agreements` table with 15 columns + indexes
- Extends `service_jobs` with `contract_visit_id`, `recurring_source_id`
- Extends `service_plan_visits` with `field_service_agreement_id`, `sale_line_id`

---

## Event Wiring

### New Events (6)

| Event | Trigger |
|-------|---------|
| `FieldServiceAgreementCreated` | Agreement created from quote or manually |
| `FieldServiceAgreementUpdated` | Agreement details updated |
| `FieldServiceAgreementActivated` | Draft agreement activated |
| `FieldServiceAgreementExpired` | Agreement expired (end_date passed) |
| `FieldServiceAgreementRenewed` | Agreement renewed (successor created) |
| `FieldServiceAgreementCancelled` | Agreement terminated |

All registered in `EventServiceProvider` under the FSM sale agreement section.

### Existing Events (unchanged)

The existing `FieldServiceAgreementSaleCreated`, `FieldServiceAgreementSaleActivated`, `FieldServiceAgreementSaleExtended` events remain and cover the **Odoo sale order → agreement** signal. The new events cover the **FieldServiceAgreement lifecycle** (distinct entity).

---

## Service: `FieldServiceAgreementService`

**Path:** `app/Services/Work/FieldServiceAgreementService.php`

| Method | Purpose |
|--------|---------|
| `createAgreementFromQuote(Quote, array)` | Promotes a quote into a FieldServiceAgreement |
| `attachAgreementToPremises(FSA, Premises)` | Links agreement to a site location |
| `generateVisitsFromAgreement(FSA, limit)` | Projects ServicePlanVisit records per frequency |
| `generateJobsFromAgreement(FSA)` | Creates ServiceJob records for pending visits |
| `syncAgreementBillingSchedule(FSA, cycle)` | Updates billing_cycle and fires event |
| `terminateAgreement(FSA, reason)` | Cancels agreement, fires Cancelled event |
| `expireAgreement(FSA)` | Marks agreement expired, fires Expired event |
| `renewAgreement(FSA, overrides)` | Creates successor agreement, marks old as renewed |
| `activateAgreement(FSA)` | Activates draft, fires Activated event |

---

## Portal Exposure

### PortalController Extensions

| Method | Route | Purpose |
|--------|-------|---------|
| `portalAgreementShow()` | `GET /portal/fsm-agreements/{agreement}` | Show agreement detail to customer |
| `portalAgreementInvoices()` | `GET /portal/fsm-agreements/{agreement}/invoices` | Agreement-related invoices |
| `portalAgreementVisits()` | `GET /portal/fsm-agreements/{agreement}/visits` | Agreement visit schedule |

### Portal Views

| View | Path |
|------|------|
| Agreement list (FSA) | `portal/agreements/index.blade.php` |
| Agreement detail | `portal/agreements/show.blade.php` |

---

## Dashboard Routes (FieldServiceAgreementController)

**Controller:** `app/Http/Controllers/Core/Work/FieldServiceAgreementController.php`

| Route | Name | Purpose |
|-------|------|---------|
| `GET /dashboard/work/fsm-agreements` | `dashboard.work.fsm-agreements.index` | List all FSAs |
| `GET /dashboard/work/fsm-agreements/{agreement}` | `dashboard.work.fsm-agreements.show` | Show FSA detail |
| `POST /dashboard/work/fsm-agreements/{agreement}/renew` | `dashboard.work.fsm-agreements.renew` | Renew FSA |
| `POST /dashboard/work/fsm-agreements/{agreement}/terminate` | `dashboard.work.fsm-agreements.terminate` | Terminate FSA |

---

## Agreement Lifecycle Automation

```
[Draft]
   ↓ activateAgreement()
[Active]
   ↓ generateVisitsFromAgreement()
[Visits projected: ServicePlanVisit × N]
   ↓ generateJobsFromAgreement()  (if auto_generate_jobs)
[Jobs created: ServiceJob × N]
   ↓ (natural expiry or renewAgreement())
[Expired | Renewed]
   ↓ terminateAgreement()  (manual)
[Cancelled]
```

---

## Recurring Execution Integration

- `ServiceJob.recurring_source_id` → traces job back to originating `FieldServiceAgreement`
- `ServiceJob.contract_visit_id` → traces job to the specific `ServicePlanVisit` that spawned it
- `ServicePlanVisit.field_service_agreement_id` → agreement-driven visit
- `ServicePlanVisit.sale_line_id` → visit originated from a specific quote line

---

## Overlap With Prior Merges

| Prior Merge | Overlap | Resolution |
|-------------|---------|------------|
| `SaleRecurringAgreementService` | Uses `ServiceAgreement` (different entity) | No conflict; `FieldServiceAgreement` is a distinct contract entity |
| `fieldservice_sale_recurring_agreement` | Covers `ServiceAgreement` recurring | `FieldServiceAgreement` adds a dedicated contract-first entity |
| `TitanContracts (MODULE_04)` | `ContractEntitlement/SLA` on `ServiceAgreement` | Separate entity; no conflict |
| `FieldServiceSaleService` | Quote→job/plan pipeline | Reused `quote_id` linkage pattern |

---

## Open Risks / Deferred Work

- [ ] `portalAgreementInvoices()` uses `invoice.agreement_id` — may need Invoice model extension if not present
- [ ] `QuoteItem.field_service_agreement_id` FK is not enforced in migration (added lazily to avoid breaking existing quote items)
- [ ] `auto_generate_jobs` flag is wired in service but not yet triggered by a scheduled command or observer
- [ ] Dashboard views use generic component patterns; full styling pass deferred
