# Finance — JobCostAllocation Model

**Date:** 2026-04-04  
**Model:** `App\Models\Money\JobCostAllocation`  
**Table:** `job_cost_allocations`

---

## Purpose

`JobCostAllocation` is the canonical cost record for Pass 3. It aggregates cost from all sources — expenses, timesheets, supplier bill lines, payroll runs, inventory usage, and manual adjustments — against a job, site, team, or customer.

---

## Table: `job_cost_allocations`

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint PK | |
| `company_id` | bigint | Tenancy; global scope applied |
| `service_job_id` | bigint nullable | FK → `service_jobs` |
| `site_id` | bigint nullable | Site scope |
| `team_id` | bigint nullable | Team scope |
| `customer_id` | bigint nullable | Customer scope |
| `supplier_id` | bigint nullable | Supplier reference |
| `source_type` | string | See `SOURCE_TYPES` |
| `source_id` | bigint nullable | Polymorphic source record ID |
| `cost_type` | string | See `COST_TYPES` |
| `amount` | decimal(12,2) | Monetary amount |
| `quantity` | decimal(10,4) nullable | Optional units (hours, kg, etc.) |
| `unit_cost` | decimal(12,2) nullable | Rate per unit |
| `description` | text nullable | Human-readable description |
| `allocated_at` | date | Date cost was incurred |
| `posted` | boolean | Whether posted to ledger |
| `posted_at` | timestamp nullable | When posted |
| `journal_entry_id` | bigint nullable | FK → `journal_entries` |
| `created_by` | bigint nullable | FK → `users` |
| `notes` | text nullable | Internal notes |
| `deleted_at` | timestamp nullable | Soft delete |

---

## Constants

### `SOURCE_TYPES`
```
expense, supplier_bill_line, timesheet, payroll_run, inventory_usage, asset_usage, manual_adjustment
```

### `COST_TYPES`
```
labour, material, equipment, subcontractor, overhead, reimbursable, admin
```

---

## Key Scopes

| Scope | Usage |
|-------|-------|
| `forJob($jobId)` | Filter by `service_job_id` |
| `forSite($siteId)` | Filter by `site_id` |
| `forTeam($teamId)` | Filter by `team_id` |
| `forCustomer($customerId)` | Filter by `customer_id` |
| `unposted()` | Filter where `posted = false` |
| `bySourceType($type)` | Filter by `source_type` |
| `byCostType($type)` | Filter by `cost_type` |

---

## Relationships

| Relation | Type | Target |
|----------|------|--------|
| `serviceJob()` | `BelongsTo` | `ServiceJob` |
| `journalEntry()` | `BelongsTo` | `JournalEntry` |
| `createdBy()` | `BelongsTo` | `User` |
| `source()` | `MorphTo` | Polymorphic source record |

---

## Tenancy

`JobCostAllocation` uses `BelongsToCompany`. The global scope automatically filters by the authenticated user's `company_id`. Use `withoutGlobalScope('company')` for cross-tenant admin queries.

---

## Usage

```php
// Create via service
$allocation = app(JobCostingService::class)->allocateManual([
    'service_job_id' => $job->id,
    'cost_type'      => 'labour',
    'source_type'    => 'manual_adjustment',
    'amount'         => 500.00,
    'allocated_at'   => today()->toDateString(),
], $companyId, $userId);

// Query
$total = JobCostAllocation::forJob($job->id)->sum('amount');
```
