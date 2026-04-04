# Finance — Cost Posting Rules

**Date:** 2026-04-04

---

## Overview

These rules define the accounting journal entries generated when each cost event occurs in the system.

---

## Rules

### Approved Expense

```
Dr  Expense Account (account_code from ExpenseCategory)
Cr  Accounts Payable  OR  Bank (depending on payment method)
```

- Source: `Expense` model
- Trigger: expense status changed to `approved`
- Journal source_type: `expense`

---

### Supplier Bill

```
Dr  Expense / Materials Account
Cr  Accounts Payable (supplier)
```

- Source: `SupplierBill` model
- Trigger: `SupplierBillRecorded` event → `AccountingService::postSupplierBillRecorded()`
- Journal source_type: `App\Models\Money\SupplierBill`

---

### Payroll Run

```
Dr  WAGES_EXPENSE          (total_gross)
Cr  PAYROLL_PAYABLE         (total_net)
Cr  TAX_WITHHOLDING_PAYABLE (total_tax)
Cr  SUPER_PAYABLE           (gross - net - tax, if > 0)
```

- Source: `Payroll` model
- Trigger: payroll approved → `PayrollPostingService::postPayrollRun()`
- Journal source_type: `payroll`

---

### Inventory Issue to Job (Future)

```
Dr  Cost of Goods / Job Cost Account
Cr  Inventory Asset Account
```

- Source: `StockMovement` (planned)
- Trigger: `MaterialIssuedToJob` event (stub, not wired)
- Journal source_type: `inventory_usage` (planned)

---

### Manual Cost Adjustment

```
Dr  Cost Account (per cost_type)
Cr  Clearing Account  OR  direct journal (depends on configuration)
```

- Source: `JobCostAllocation` with `source_type = 'manual_adjustment'`
- Trigger: manual entry via `JobCostingService::allocateManual()`
- Journal: requires explicit `JournalEntry` creation (not automatic)
- Journal source_type: `manual_adjustment`

---

## Notes

- All journals are written via `AccountingService` methods.
- The `JobCostAllocation.posted` flag tracks whether the allocation has been reflected in the ledger.
- Unposted allocations can be queried with `JobCostAllocation::unposted()`.
