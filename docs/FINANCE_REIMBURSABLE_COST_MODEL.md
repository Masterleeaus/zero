# Finance — Reimbursable Cost Model

**Date:** 2026-04-04

---

## Overview

Reimbursable costs are expenses incurred on behalf of a customer that are expected to be billed back. Pass 3 introduces first-class support for flagging and tracking these costs.

---

## `reimbursable_to_customer` Flag on Expense

The `reimbursable_to_customer` boolean field on `expenses` marks an expense as customer-reimbursable.

```php
Expense::create([
    'cost_bucket'              => 'reimbursable',
    'reimbursable_to_customer' => true,
    'service_job_id'           => $job->id,
    // ...
]);
```

When this expense is allocated via `JobCostingService::allocateExpense()`, the resulting `JobCostAllocation` will have `cost_type = 'reimbursable'` (because `reimbursable` bucket maps to `reimbursable` cost type).

---

## `allocation_reference` Field

`allocation_reference` is a free-text cross-reference token used to link expenses to external billing systems, PO numbers, or customer references.

```php
'allocation_reference' => 'CUST-REF-2026-042'
```

This field has no enforced format — it is for human/system cross-referencing only.

---

## How Reimbursable Costs Connect to Customer Billing (Future)

The current implementation flags and tracks reimbursable costs. Full customer billing automation is planned for a future pass.

Intended flow:

```
Expense (reimbursable_to_customer=true)
  → JobCostAllocation (cost_type=reimbursable, source_type=expense)
    → [Future] Invoice line auto-generated for reimbursable allocations
      → Customer Invoice (reimbursable line items)
```

To query all reimbursable allocations for a job:

```php
JobCostAllocation::forJob($jobId)
    ->byCostType('reimbursable')
    ->get();
```

---

## Cost Type: `reimbursable`

`JobCostAllocation::COST_TYPES` includes `reimbursable` as a distinct cost type. This allows profitability calculations to separate internally-absorbed costs from pass-through costs.
