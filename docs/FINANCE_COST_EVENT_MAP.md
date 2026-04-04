# Finance — Cost Event Map

**Date:** 2026-04-04

---

## Events & Listeners

### `TimesheetApproved` → `PostTimesheetApprovedToJobCost`

| Item | Value |
|------|-------|
| Event | `App\Events\Money\TimesheetApproved` |
| Listener | `App\Listeners\Money\PostTimesheetApprovedToJobCost` |
| Trigger | Timesheet submission status changed to `approved` |
| Action | Calls `LaborCostingService::allocateTimesheetToJob()` to create `JobCostAllocation` |
| Data | `TimesheetSubmission $submission` passed via event constructor |

---

### `PayrollInputFinalized` → `PostPayrollInputFinalizedToLedger`

| Item | Value |
|------|-------|
| Event | `App\Events\Money\PayrollInputFinalized` |
| Listener | `App\Listeners\Money\PostPayrollInputFinalizedToLedger` |
| Trigger | Payroll run finalized/approved |
| Action | Calls `PayrollPostingService::postPayrollRun()` to create `JournalEntry` |
| Data | `Payroll $payroll` passed via event constructor |

---

### `CostAllocationCreated` (stub)

| Item | Value |
|------|-------|
| Event | `App\Events\Money\CostAllocationCreated` |
| Listener | None wired (stub) |
| Trigger | A new `JobCostAllocation` is persisted |
| Planned Action | Notify job stakeholders, trigger downstream analytics update |

---

### `MaterialIssuedToJob` (stub)

| Item | Value |
|------|-------|
| Event | `App\Events\Money\MaterialIssuedToJob` |
| Listener | None wired (stub) |
| Trigger | Inventory item consumed against a service job |
| Planned Action | `MaterialCostingService::allocateInventoryUsage()` — auto cost allocation |
| Status | Awaiting inventory consumption tracking (Pass 4) |

---

## Event Registration

Events are registered in `App\Providers\EventServiceProvider` under the `$listen` array:

```php
TimesheetApproved::class         => [PostTimesheetApprovedToJobCost::class],
PayrollInputFinalized::class     => [PostPayrollInputFinalizedToLedger::class],
```

`CostAllocationCreated` and `MaterialIssuedToJob` are declared but have no listeners registered yet.
