# MODULE 09 — ExecutionFinanceLayer: Job Profitability Engine

**Label:** `titan-module` `finance` `profitability` `billing` `costing`
**Milestone:** TitanZero Capability Expansion Pack v1
**Priority:** Medium-High

---

## Overview

Build the **ExecutionFinanceLayer** — a real-time job profitability and financial execution engine that tracks costs, revenue, margins, and billing outcomes at the individual job level, rolled up to contract, customer, premises, and company level. Every job knows its financial position before it's completed.

ExecutionFinanceLayer extends the existing `JobBillingService` and `QuoteService`, integrates with `TitanContracts` (Module 04) for entitlement-driven billing, and produces the financial data that feeds `TitanPredict` (Module 07) for profitability forecasting.

---

## Pre-Scan Requirement (MANDATORY — run before any implementation pass)

1. Read `app/Services/Work/JobBillingService.php` — understand existing billing logic fully
2. Read `app/Services/Money/QuoteService.php` — quote and pricing patterns
3. Read `app/Models/Work/ServiceJob.php` — all financial fields present
4. Read `app/Models/Work/ServiceAgreement.php` — contract billing fields (Module 04 extended these)
5. Read `app/Models/Work/WeeklyTimesheet.php` — labour cost data source
6. Read `app/Models/Crm/Customer.php` and `Deal.php` — customer-level financial context
7. Read `database/migrations/` — all money, billing, quote table schemas
8. Read `docs/nexuscore/` — scan for billing, profitability, costing, or financial design docs
9. Read `docs/titancore/` — scan for finance layer or commercial architecture docs
10. Read `CodeToUse/work/` — scan ALL files for billing, costing, or financial entity files

---

## Canonical Models to Extend / Reference

- `app/Models/Work/ServiceJob.php` — primary financial subject
- `app/Services/Work/JobBillingService.php` — extend, do not replace
- `app/Services/Money/QuoteService.php` — extend, do not replace
- `app/Models/Work/WeeklyTimesheet.php` — labour input
- `app/Models/Work/ServiceAgreement.php` — contract billing terms

---

## Required Output Artifacts

### Migrations
- `database/migrations/YYYY_MM_DD_create_execution_finance_tables.php`
  - `job_cost_records` — itemised cost tracking per job: `id`, `company_id`, `job_id`, `cost_type` (labour|materials|travel|subcontract|overhead|other), `description`, `quantity` (decimal 10,3), `unit_cost` (decimal 12,4), `total_cost` (decimal 12,2), `recorded_by` (user_id nullable), `cost_date`, `is_billable` (bool), `created_at`, `updated_at`
  - `job_revenue_records` — itemised revenue per job: `id`, `company_id`, `job_id`, `revenue_type` (labour|materials|call_out|surcharge|contract_allocation|other), `description`, `quantity` (decimal 10,3), `unit_price` (decimal 12,4), `total_revenue` (decimal 12,2), `billing_source` (agreement|quote|ad_hoc|time_and_materials), `agreement_id` (nullable), `is_invoiced` (bool default false), `invoiced_at` (nullable), `created_at`, `updated_at`
  - `job_financial_summaries` — denormalised snapshot per job (recalculated on change): `job_id` (unique), `company_id`, `total_cost`, `total_revenue`, `gross_margin`, `gross_margin_pct` (decimal 6,4), `labour_cost`, `materials_cost`, `travel_cost`, `labour_revenue`, `materials_revenue`, `is_profitable` (bool), `calculated_at`
  - `financial_rollups` — aggregated financial performance: `company_id`, `rollup_type` (customer|premises|agreement|technician|job_type|territory|month), `rollup_key` (string — the id or period), `period_start`, `period_end`, `job_count`, `total_cost`, `total_revenue`, `gross_margin`, `gross_margin_pct`, `calculated_at`
  - Extend `service_jobs` table (ALTER, `hasColumn` guards): `quoted_amount` (decimal 12,2 nullable), `actual_cost` (decimal 12,2 nullable), `actual_revenue` (decimal 12,2 nullable), `margin_pct` (decimal 6,4 nullable), `billing_status` (unbilled|partial|invoiced|paid|written_off default 'unbilled'), `invoiced_at`
- Use `Schema::hasTable()` / `Schema::hasColumn()` guards

### Models
- `app/Models/Finance/JobCostRecord.php` — with `BelongsToCompany`, `BelongsTo(ServiceJob)`
- `app/Models/Finance/JobRevenueRecord.php` — with `BelongsToCompany`, `BelongsTo(ServiceJob)`
- `app/Models/Finance/JobFinancialSummary.php` — with `BelongsTo(ServiceJob)`, recalculation method
- `app/Models/Finance/FinancialRollup.php` — with `BelongsToCompany`, rollup type scopes

### Services
- `app/Services/Finance/JobCostingService.php`
  - `recordLabourCost(ServiceJob $job, User $tech, float $hours, float $hourlyRate): JobCostRecord`
  - `recordMaterialsCost(ServiceJob $job, array $items): Collection`
  - `recordTravelCost(ServiceJob $job, float $distance, float $ratePerKm): JobCostRecord`
  - `getTotalCost(ServiceJob $job): float`
  - `getCostBreakdown(ServiceJob $job): array`
- `app/Services/Finance/JobRevenueService.php`
  - `recordFromAgreement(ServiceJob $job, ServiceAgreement $agreement): JobRevenueRecord`
  - `recordAdHocRevenue(ServiceJob $job, array $lineItems): Collection`
  - `applyCallOutCharge(ServiceJob $job): JobRevenueRecord`
  - `getTotalRevenue(ServiceJob $job): float`
  - `getRevenueBreakdown(ServiceJob $job): array`
- `app/Services/Finance/JobProfitabilityService.php`
  - `calculateSummary(ServiceJob $job): JobFinancialSummary`
  - `refreshSummary(ServiceJob $job): JobFinancialSummary`
  - `isJobProfitable(ServiceJob $job): bool`
  - `getAtRiskJobs(int $companyId, float $marginThreshold = 0.0): Collection`
  - `getUnprofitableJobs(int $companyId, Carbon $since): Collection`
- `app/Services/Finance/FinancialRollupService.php`
  - `rollupForCustomer(Customer $customer, Carbon $periodStart, Carbon $periodEnd): FinancialRollup`
  - `rollupForAgreement(ServiceAgreement $agreement): FinancialRollup`
  - `rollupForPeriod(int $companyId, Carbon $month): FinancialRollup`
  - `refreshAllRollups(int $companyId): void` — scheduled command target
- Extend `app/Services/Work/JobBillingService.php`:
  - Add `applyFinanceLayerOnBill(ServiceJob $job): void` — hook that calls `JobProfitabilityService::refreshSummary()`

### Events
- `app/Events/Finance/JobCostRecorded.php`
- `app/Events/Finance/JobFinancialSummaryUpdated.php`
- `app/Events/Finance/UnprofitableJobDetected.php`
- `app/Events/Finance/JobInvoiced.php`

### Listeners
- `app/Listeners/Finance/RecalculateFinancialSummaryOnCostChange.php`
- `app/Listeners/Finance/NotifyOnUnprofitableJob.php`
- `app/Listeners/Finance/RecordRevenueOnJobBilled.php` — fires on existing billing events

### Signals
- Emit via `SignalDispatcher`: `finance.cost_recorded`, `finance.summary_updated`, `finance.unprofitable_detected`, `finance.invoiced`

### Controllers / Routes
- `app/Http/Controllers/Finance/JobFinanceController.php`
  - `summary(ServiceJob $job)` — financial summary
  - `costs(ServiceJob $job)` — itemised cost breakdown
  - `revenue(ServiceJob $job)` — itemised revenue breakdown
  - `atRisk(Request $request)` — jobs below margin threshold
  - `rollup(Request $request)` — financial rollup by type and period
- Register in `routes/core/` as new `finance.php` route file

### Tests
- `tests/Unit/Services/Finance/JobCostingServiceTest.php`
- `tests/Unit/Services/Finance/JobProfitabilityServiceTest.php`
- `tests/Feature/Finance/JobFinanceControllerTest.php`

### Docs Report
- `docs/modules/MODULE_09_ExecutionFinanceLayer_report.md` — cost type catalogue, revenue type catalogue, margin calculation methodology, rollup strategy, TitanPredict integration points

### FSM Update
- Update `fsm_module_status.json` — set `execution_finance_layer` to `installed`

---

## Architecture Notes

- `JobFinancialSummary` is a denormalised snapshot — it must be recalculated (not updated in-place) whenever cost or revenue records change
- Margin = (total_revenue - total_cost) / total_revenue — handle zero revenue: if revenue = 0 and cost > 0, margin = -infinity (flag as critical)
- Labour cost source: `WeeklyTimesheet` records + dispatch assignment travel estimates from Module 01
- Revenue source hierarchy: (1) agreement entitlement allocation, (2) quote line items, (3) ad-hoc billing
- All financial mutations must be within database transactions — no partial cost/revenue states
- `FinancialRollup` is a materialised view equivalent — scheduled daily recalculation via console command
- Do NOT modify `JobBillingService` core logic — extend via hooks/listeners to avoid breaking existing billing flows
- Must respect `company_id` scoping throughout — financial data is highly sensitive

---

## References

- `app/Services/Work/JobBillingService.php`
- `app/Services/Money/QuoteService.php`
- `app/Models/Work/ServiceJob.php`
- `app/Models/Work/ServiceAgreement.php`
- `app/Models/Work/WeeklyTimesheet.php`
- `app/Models/Crm/Customer.php`
- `app/Services/Work/DispatchService.php` (Module 01 — travel cost data)
- `app/Services/Work/ContractEntitlementService.php` (Module 04 — billing source)
- `app/Titan/Signals/SignalDispatcher.php`
- `app/Titan/Signals/AuditTrail.php`
- `docs/nexuscore/` (billing, finance, profitability docs)
- `docs/titancore/` (finance layer architecture)
- `CodeToUse/work/` (billing/costing entities)
