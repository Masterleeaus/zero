# Finance — Payroll Posting Bridge

**Date:** 2026-04-04  
**Service:** `App\Services\TitanMoney\PayrollPostingService`

---

## Purpose

`PayrollPostingService` translates an approved `Payroll` run into a structured accounting journal entry. It generates the debit/credit lines for wages expense, net pay payable, tax withholding, and any superannuation/benefit deductions.

---

## Methods

### `buildPostingPayload(Payroll $payroll): array`

Returns a structured payload array ready for journal posting. Does **not** write to the database.

```php
$payload = $service->buildPostingPayload($payroll);
// [
//   'payroll_id'  => 7,
//   'company_id'  => 42,
//   'description' => 'Payroll run #7',
//   'lines'       => [ ... ]
// ]
```

---

### `postPayrollRun(Payroll $payroll): JournalEntry`

Posts the payroll journal via `AccountingService::postPayrollApproved()`. Returns the created `JournalEntry`.

---

### `preparePayrollForPosting(Payroll $payroll): array`

Validates that the payroll is ready for posting. Returns:

```php
['ready' => bool, 'errors' => string[]]
```

Checks:
- Payroll status must be `approved`
- `total_gross` must be > 0
- At least one payroll line must exist

---

### `getPostingStatus(Payroll $payroll): string`

Returns `'posted'`, `'failed'`, or `'pending'` based on whether a matching `JournalEntry` (source_type=`payroll`) exists.

---

## Posting Line Categories

| Account Code | Direction | Source |
|-------------|-----------|--------|
| `WAGES_EXPENSE` | Debit | `Payroll.total_gross` |
| `PAYROLL_PAYABLE` | Credit | `Payroll.total_net` |
| `TAX_WITHHOLDING_PAYABLE` | Credit | `Payroll.total_tax` (if > 0) |
| `SUPER_PAYABLE` | Credit | `total_gross - total_net - total_tax` (if > 0) |

The `SUPER_PAYABLE` line captures salary sacrifice, superannuation, and other deductions not explicitly broken out in the payroll run.

---

## Integration with AccountingService

`PayrollPostingService` depends on `AccountingService` (injected via constructor). Posting is delegated to:

```php
$this->accounting->postPayrollApproved($payroll);
```

---

## Typical Payroll Posting Flow

```
PayrollService::approve()
  → PostPayrollInputFinalizedToLedger (listener)
    → PayrollPostingService::postPayrollRun()
      → AccountingService::postPayrollApproved()
        → JournalEntry created (status=posted)
```
