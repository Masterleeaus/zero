# MODULE 09 — ExecutionFinanceLayer

**Status:** Installed  
**Installed At:** 2026-04-04  
**Domain:** Finance / Work  

---

## Purpose

The ExecutionFinanceLayer connects field-service job execution to the financial domain. It captures real-time cost and revenue data at the job level, computes profitability summaries, and aggregates financial performance into rollup reports by customer, agreement, and period.

---

## Components Created

### Migration
`database/migrations/2026_04_04_900300_create_execution_finance_tables.php`

Tables created:
| Table | Purpose |
|---|---|
| `job_cost_records` | Labour, materials, travel, subcontract, overhead costs per job |
| `job_revenue_records` | Revenue lines per job (agreement, ad-hoc, time & materials) |
| `job_financial_summaries` | Aggregated cost/revenue/margin per job (unique by job_id) |
| `financial_rollups` | Aggregated rollups by customer, agreement, territory, period |

`service_jobs` extended with: `quoted_amount`, `actual_cost`, `actual_revenue`, `margin_pct`, `billing_status` (all guarded with `hasColumn`).

---

### Models

| Model | Namespace | Description |
|---|---|---|
| `JobCostRecord` | `App\Models\Finance` | Individual cost line; uses BelongsToCompany |
| `JobRevenueRecord` | `App\Models\Finance` | Individual revenue line; uses BelongsToCompany |
| `JobFinancialSummary` | `App\Models\Finance` | Unique per job; has costRecords/revenueRecords via job_id |
| `FinancialRollup` | `App\Models\Finance` | Multi-type aggregation; uses BelongsToCompany |

`ServiceJob` extended with relations: `costRecords()`, `revenueRecords()`, `financialSummary()`.

---

### Services

| Service | Key Methods |
|---|---|
| `JobCostingService` | `recordLabourCost`, `recordMaterialsCost`, `recordTravelCost`, `getTotalCost`, `getCostBreakdown` |
| `JobRevenueService` | `recordFromAgreement`, `recordAdHocRevenue`, `getTotalRevenue` |
| `JobProfitabilityService` | `calculateSummary`, `refreshSummary`, `isJobProfitable`, `getAtRiskJobs`, `getUnprofitableJobs` |
| `FinancialRollupService` | `rollupForCustomer`, `rollupForAgreement`, `rollupForPeriod`, `refreshAllRollups` |

All mutation methods use `DB::transaction()`.

---

### Events

| Event | Payload |
|---|---|
| `App\Events\Finance\JobCostRecorded` | `$costRecord: JobCostRecord` |
| `App\Events\Finance\JobFinancialSummaryUpdated` | `$summary: JobFinancialSummary` |
| `App\Events\Finance\UnprofitableJobDetected` | `$summary: JobFinancialSummary` |
| `App\Events\Finance\JobInvoiced` | `$job: ServiceJob, $invoice: Invoice` |

---

### Listeners

| Listener | Triggered By | Action |
|---|---|---|
| `RecalculateFinancialSummaryOnCostChange` | `JobCostRecorded` | Calls `JobProfitabilityService::calculateSummary` |
| `NotifyOnUnprofitableJob` | `UnprofitableJobDetected` | Logs warning via `Log::warning` |
| `RecordRevenueOnJobBilled` | `JobInvoiced` | Creates `JobRevenueRecord` from invoice total |

---

### Controller

`App\Http\Controllers\Finance\JobFinanceController`

| Route | Method | Action |
|---|---|---|
| `GET /dashboard/finance/jobs/{job}/summary` | `summary` | Calculate & return job financial summary |
| `GET /dashboard/finance/jobs/{job}/costs` | `costs` | Return cost breakdown by type |
| `GET /dashboard/finance/jobs/{job}/revenue` | `revenue` | Return revenue total and records |
| `GET /dashboard/finance/at-risk` | `atRisk` | Jobs that are unprofitable or below margin threshold |
| `POST /dashboard/finance/rollup` | `rollup` | Trigger full rollup refresh for company |

All routes require `auth` middleware.

---

### JobBillingService Extension

`applyFinanceLayerOnBill(ServiceJob $job, Invoice $invoice): void` added.  
Dispatches `App\Events\Finance\JobInvoiced` to trigger the revenue recording listener.  
Existing billing logic is untouched — this is an additive hook only.

---

### Tests

| File | Type | Coverage |
|---|---|---|
| `tests/Unit/Services/Finance/JobCostingServiceTest.php` | Unit | Labour/travel cost recording, total calculation, breakdown |
| `tests/Unit/Services/Finance/JobProfitabilityServiceTest.php` | Unit | Profitable/unprofitable/zero-revenue summaries, isJobProfitable |
| `tests/Feature/Finance/JobFinanceControllerTest.php` | Feature | Summary/costs/at-risk endpoints, unauthenticated access denied |

---

## Integration Map

| Connection | Detail |
|---|---|
| Tables | `job_cost_records`, `job_revenue_records`, `job_financial_summaries`, `financial_rollups` |
| FK to | `service_jobs`, `service_agreements`, `users` |
| Events | `JobCostRecorded`, `JobFinancialSummaryUpdated`, `UnprofitableJobDetected`, `JobInvoiced` |
| Providers | Registered in `EventServiceProvider` |
| Routes | `routes/core/finance.routes.php` |
| Extends | `ServiceJob` (relations), `JobBillingService` (hook method) |
| Domain | Finance ← Work ← Money |
