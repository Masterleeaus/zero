# Finance — Profitability Layer

**Date:** 2026-04-04  
**Service:** `App\Services\TitanMoney\ProfitabilityService`

---

## Purpose

`ProfitabilityService` calculates gross margin by comparing total `JobCostAllocation` amounts (cost) against revenue sourced from invoices or `ServiceJob.actual_revenue`.

---

## Methods

### `forJob(ServiceJob $job): array`

Returns profitability for a single job.

```php
$result = $service->forJob($job);
// ['gross_revenue' => 1200.00, 'gross_cost' => 800.00, 'gross_margin' => 400.00, 'margin_pct' => 33.33]
```

---

### `forSite(int $siteId, ?Carbon $from, ?Carbon $to): array`

Aggregates all job costs and invoices for jobs at a site within an optional date range.

---

### `forTeam(int $teamId, ?Carbon $from, ?Carbon $to): array`

Aggregates all job costs and invoices for jobs belonging to a team.

---

### `forCustomer(int $customerId, ?Carbon $from, ?Carbon $to): array`

Aggregates across all jobs for a customer.

---

### `forPeriod(int $companyId, Carbon $from, Carbon $to): array`

Company-wide profitability for allocations within a date range. Useful for P&L period views.

---

## Revenue Sourcing

Revenue is resolved per job in this priority order:

1. **`ServiceJob.actual_revenue`** — used if > 0
2. **Invoice totals** — summed from `invoices` where `service_job_id = $job->id` and `status IN ('paid', 'sent', 'approved')`

---

## Output Structure

```php
[
    'gross_revenue' => float,  // Total revenue
    'gross_cost'    => float,  // Total allocated costs
    'gross_margin'  => float,  // gross_revenue - gross_cost
    'margin_pct'    => float,  // (gross_margin / gross_revenue) × 100, or 0 if no revenue
]
```

All values are rounded to 2 decimal places.

---

## Cost Sourcing

Cost is sourced from `JobCostAllocation` using the appropriate scope:

- `forJob()` → `JobCostAllocation::forJob($job->id)->sum('amount')`
- `forSite()` → `JobCostAllocation::forSite($siteId)->sum('amount')` with date filter
- `forTeam()` → `JobCostAllocation::forTeam($teamId)->sum('amount')` with date filter
- `forCustomer()` → `JobCostAllocation::forCustomer($customerId)->sum('amount')` with date filter
- `forPeriod()` → filtered by `company_id` and `allocated_at` date range
