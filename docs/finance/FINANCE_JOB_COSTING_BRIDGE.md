# FINANCE — JOB COSTING BRIDGE

## Columns Added (migration 600200)

| Table               | Column         | Notes                      |
|---------------------|----------------|----------------------------|
| purchase_orders     | service_job_id | nullable bigint            |
| supplier_bill_lines | service_job_id | nullable bigint            |

## Usage

```php
// Assign a PO to a job
PurchaseOrder::create([
    'service_job_id' => $job->id,
    ...
]);

// Assign a bill line to a job
SupplierBillLine::create([
    'service_job_id' => $job->id,
    'account_id'     => $expenseAccount->id,
    'amount'         => 150.00,
    ...
]);
```

## Future Integration

When the JobCost domain is implemented:
- `service_job_id` will reference `service_jobs.id`
- A `JobCostEntry` will be created for each bill line with a `service_job_id`
- PO costs will roll up to job margin reports

## Current State

The columns exist and are nullable. No FK constraint is enforced yet
(ServiceJob table may not be present in all environments).
