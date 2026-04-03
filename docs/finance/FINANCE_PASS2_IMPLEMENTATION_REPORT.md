# FINANCE PASS 2 — IMPLEMENTATION REPORT

**Date:** 2026-04-03
**Domain:** Finance / Accounts Payable
**Migration:** 600200

---

## Overview

Finance Domain Pass 2 delivers the full supplier and accounts-payable (AP) pipeline:
- Supplier registry (extended from Inventory domain)
- Purchase orders (extended from Inventory domain, job-cost column added)
- Supplier bills (AP documents with journal auto-posting)
- Supplier payments (AP clearing with journal posting)

---

## Files Created / Modified

### Migration
- `database/migrations/2026_04_03_600200_create_finance_ap_tables.php`
  - Adds `default_account_id` to `suppliers`
  - Adds `service_job_id` to `purchase_orders`
  - Creates `supplier_bills`
  - Creates `supplier_bill_lines`
  - Creates `supplier_payments`

### Models (app/Models/Money/)
- `SupplierBill.php` — AP document (reuses Inventory\Supplier)
- `SupplierBillLine.php` — Line items with account + job-cost linkage
- `SupplierPayment.php` — Payment against a bill

### Services (app/Services/TitanMoney/)
- `SupplierBillService.php` — createBill / updateBill / recordPayment / outstandingBills
- `AccountingService.php` — extended with postSupplierBillRecorded() + postSupplierPaymentRecorded()

### Events (app/Events/Money/)
- `SupplierCreated.php`
- `PurchaseOrderIssued.php`
- `SupplierBillRecorded.php`
- `SupplierPaymentRecorded.php`

### Observer (app/Observers/Money/)
- `SupplierPaymentObserver.php` — auto-posts Dr AP / Cr Bank on payment.created

### Controllers (app/Http/Controllers/Core/Money/)
- `SupplierController.php` — AP context, wraps Inventory\Supplier
- `PurchaseOrderController.php` — AP context, wraps Inventory\PurchaseOrder
- `SupplierBillController.php` — full CRUD for supplier bills
- `SupplierPaymentController.php` — payment recording

### Policies (app/Policies/)
- `SupplierPolicy.php`
- `PurchaseOrderPolicy.php`
- `SupplierBillPolicy.php`
- `SupplierPaymentPolicy.php`

### Routes (routes/core/money.routes.php)
- `money.suppliers.*`
- `money.purchase-orders.*`
- `money.supplier-bills.*`
- `money.supplier-payments.store`

### Views (resources/views/default/panel/user/money/)
- `suppliers/index.blade.php`, `form.blade.php`, `show.blade.php`
- `purchase-orders/index.blade.php`, `form.blade.php`, `show.blade.php`
- `supplier-bills/index.blade.php`, `form.blade.php`, `show.blade.php`

### AuthServiceProvider
- Registered 4 new policies
- Fixed duplicate `use` statements

### Tests
- `tests/Feature/Money/FinancePass2APTest.php` — 12 test cases

---

## Journal Posting Rules

| Transaction           | Debit                   | Credit                |
|-----------------------|-------------------------|-----------------------|
| Supplier Bill posted  | Operating Expenses 6000 | Accounts Payable 2000 |
| Supplier Payment made | Accounts Payable 2000   | Bank 1000             |

---

## Integration Points

- **Inventory**: Reuses `App\Models\Inventory\Supplier` and `App\Models\Inventory\PurchaseOrder` — no duplication
- **Job Costing**: `purchase_orders.service_job_id` + `supplier_bill_lines.service_job_id` hooks ready
- **Chart of Accounts**: Auto-creates accounts 2000 (AP) + 6000 (Expenses) + 1000 (Bank) if absent
- **Finance Phase 1**: Extends `AccountingService` with 2 new posting methods

---

## Next Steps

- Phase 3: Payroll, Assets, Financial Reporting
- Wire SupplierPaymentObserver in a service provider boot()
- Connect `service_job_id` to actual ServiceJob model when job-costing domain is finalised
