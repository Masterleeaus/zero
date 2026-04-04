# Finance Expense Normalization

**Date:** 2026-04-04  
**Migration:** `2026_04_04_600300_finance_pass3_expense_normalization_and_job_costing`

---

## Overview

Pass 3 extends the `expenses` table with classification and routing fields. These allow expenses to be:
- Attributed to a specific job, site, team, or supplier
- Mapped to a cost type for `JobCostAllocation`
- Flagged for customer reimbursement
- Cross-referenced against external references

---

## New Fields on `expenses`

| Field | Type | Default | Purpose |
|-------|------|---------|---------|
| `cost_bucket` | `string nullable` | `null` | Classification bucket (see below) |
| `service_job_id` | `bigint nullable` | `null` | FK → `service_jobs.id` (nullOnDelete) |
| `site_id` | `bigint nullable` | `null` | Site reference for site-level reporting |
| `team_id` | `bigint nullable` | `null` | Team reference for team-level reporting |
| `supplier_id` | `bigint nullable` | `null` | Supplier link (paid via supplier) |
| `reimbursable_to_customer` | `boolean` | `false` | Flag for customer billing |
| `allocation_reference` | `string nullable` | `null` | External reference token (e.g. `REF-001`) |

---

## Cost Buckets

| Bucket | Mapped Cost Type | Description |
|--------|-----------------|-------------|
| `overhead` | `overhead` | General overhead not tied to a job |
| `labor_adjacent` | `labour` | Costs alongside labor (e.g. training, PPE) |
| `reimbursable` | `reimbursable` | Customer-reimbursable costs |
| `materials` | `material` | Physical materials purchased |
| `transport` | `material` | Freight and delivery costs |
| `equipment` | `equipment` | Equipment hire or usage |
| `subcontractor` | `subcontractor` | Subcontractor invoices |
| `admin` | `admin` | Administrative costs |
| `tax_adjacent` | `overhead` | Tax and compliance costs |

Bucket-to-cost-type mapping is defined in `JobCostingService::BUCKET_TO_COST_TYPE`.

---

## Usage

### Setting normalization fields

```php
Expense::create([
    'company_id'               => $companyId,
    'title'                    => 'Copper piping',
    'amount'                   => 120.00,
    'expense_date'             => today(),
    'cost_bucket'              => 'materials',
    'service_job_id'           => $job->id,
    'reimbursable_to_customer' => true,
    'allocation_reference'     => 'JOB-2026-001',
    'status'                   => 'pending',
]);
```

### Allocating a normalized expense to a job

```php
$allocation = app(JobCostingService::class)->allocateExpense($expense);
// $allocation->cost_type === 'material'
// $allocation->source_type === 'expense'
```
