# FINANCE_OVERLAP_MATRIX.md
## Host vs Source — Finance Feature Classification

**Audit date:** 2026-04-03

### Classification Codes
| Code | Meaning |
|------|---------|
| **A** | Already exists in host — keep host, skip source |
| **B** | Exists in host but source has enrichments — merge/extend host |
| **C** | Does not exist in host — good extraction candidate |
| **D** | Exists in source but conflicts with host infra — extract logic only |
| **E** | Source feature not worth merging |

---

## Feature Matrix

| # | Finance Feature | Host Path | Source Path | Class | Duplicate? | Overlap? | Integration Value | Required Domains | Blocking Dependencies | Host-Safe Method |
|---|----------------|-----------|-------------|-------|-----------|---------|-------------------|-----------------|----------------------|-----------------|
| 1 | **Quotes** | `app/Models/Money/Quote.php`, `Controllers/Core/Money/QuoteController.php` | CommerceCore has no direct quote model (uses orders) | **A** | Yes | No | — | Customer, Tax, Invoice | None | Keep host only |
| 2 | **Invoices** | `app/Models/Money/Invoice.php`, `InvoiceController.php` | `CommerceCore/Admin/InvoiceController.php` (order-based) | **A** | Yes | Partial | — | Customer, Payment, CreditNote | None | Keep host only |
| 3 | **Payments** | `app/Models/Money/Payment.php`, `PaymentController.php` | `CommerceCore/app/Models/Payment.php` | **A** | Yes | Partial | — | Invoice, BankAccount | None | Keep host only |
| 4 | **Credit Notes** | `app/Models/Money/CreditNote.php`, `CreditNoteController.php` | None in source | **A** | N/A | No | — | Invoice | None | Keep host only |
| 5 | **Expenses** | `app/Models/Money/Expense.php`, `ExpenseController.php` (with approval) | `CommerceCore/app/Models/Expense.php` (simpler, no approval) | **A** | Yes | Yes (host richer) | — | ExpenseCategory, User, ServiceJob | None | Keep host only |
| 6 | **Expense Categories** | `app/Models/Money/ExpenseCategory.php`, `ExpenseCategoryController.php` | None explicit in source | **A** | N/A | No | — | Expense | None | Keep host only |
| 7 | **Taxes** | `app/Models/Money/Tax.php`, `TaxController.php` | CommerceCore has gateway-level taxes only | **A** | No | No | — | Quote, Invoice | None | Keep host only |
| 8 | **Bank Accounts** | `app/Models/Money/BankAccount.php`, `BankAccountController.php` | `CommerceCore/app/Models/Account.php` (broader — chart of accounts) | **B** | Partial | Yes | Medium | Payment, ChartOfAccounts | ChartOfAccounts (C) | Enrich host BankAccount; link to CoA when extracted |
| 9 | **Chart of Accounts** | ❌ Missing | `CommerceCore/app/Models/Account.php` + migrations | **C** | No | No | **HIGH** | BankAccount, Journal, Transactions | None | Extract Account model as `app/Models/Money/Account.php`; adapt `BelongsToCompany` |
| 10 | **Journal Entries / Ledger** | ❌ Missing | `CommerceCore/app/Models/JournalEntry.php`, `JournalLine.php` | **C** | No | No | **HIGH** | ChartOfAccounts, Invoice, Payment, Expense | ChartOfAccounts (C) | Extract both models; wire to Money domain; adapt to `company_id` |
| 11 | **Transactions (ledger postings)** | ❌ Missing | `CommerceCore/app/Models/Transaction.php`, `AccountingService.php` | **C** | No | No | **HIGH** | Account, JournalEntry | ChartOfAccounts (C) | Extract Transaction model + AccountingService skeleton |
| 12 | **Suppliers / Vendors** | ❌ Missing | `CommerceCore/app/Models/Supplier.php` | **C** | No | No | **HIGH** | Purchase | None | Extract as `app/Models/Money/Supplier.php`; adapt tenancy |
| 13 | **Purchases / Procurement** | ❌ Missing | `CommerceCore/app/Models/Purchase.php`, `PurchaseItem.php`, `PurchaseService.php` | **C** | No | No | **HIGH** | Supplier, Expense, Asset | Supplier (C) | Extract Purchase model + controller; link to Suppliers |
| 14 | **Payroll** | ❌ Missing | `CommerceCore/app/Models/Payroll.php`, `PayrollController.php` | **C** | No | No | **HIGH** | Employee, Attendance, Leave | Employee (C) | Extract Payroll model; adapt to host team/staff model |
| 15 | **Employee Records (HR)** | ❌ Missing (TeamMember ≠ Employee) | `CommerceCore/app/Models/Employee.php` | **C** | No | Partial | **HIGH** | TeamMember (bridge), Payroll | None | Extract Employee model; bridge to host TeamMember |
| 16 | **Asset Accounting + Depreciation** | ❌ Missing (SiteAsset exists but no financials) | `CommerceCore/app/Models/Asset.php` | **C** | No | Partial | **HIGH** | Purchase, Supplier | Supplier, Purchase (C) | Extract Asset model; link to Purchase; separate from SiteAsset |
| 17 | **Finance Reporting (Accounting Reports)** | Partial (`InsightsController`) | `CommerceCore/AccountingReportController.php` + `AccountingService.php` | **B** | No | Yes | **HIGH** | Account, Transaction, Journal, Invoice, Payment, Expense | ChartOfAccounts, Journal (C) | Extend InsightsController with accounting report views after foundation is in |
| 18 | **Leave Management** | ✅ Exists (`Work/Leave.php`, `LeaveQuota`, `LeaveHistory`) | `CommerceCore/app/Models/LeaveRequest.php` | **A/B** | Yes | Yes | Low | Attendance, Employee | — | Keep host; minor enrichment if payroll needs leave deduction inputs |
| 19 | **Attendance (source)** | ✅ Exists (`Work/Attendance.php`, rich model) | `CommerceCore/app/Models/Attendance.php` (simpler) | **A** | Yes | Yes (host richer) | — | — | — | Keep host only; skip source |
| 20 | **Recurring Billing (Business)** | ❌ Missing (platform SaaS billing exists) | `CommerceCore/app/Models/Subscription.php` (store subscription) | **C** | No | No | Medium | Invoice, ServiceAgreement, Customer | Invoice, Customer | Deferred — extract after invoice/payment foundation matures |
| 21 | **Billing (SaaS platform)** | ✅ Exists (Gateways, Plans, Subscriptions) | `CommerceCore/BillingController.php` | **A** | Yes | No | — | — | — | Keep host only; skip source |
| 22 | **Cashflow / P&L Reporting** | ❌ Missing (only partial in InsightsController) | `AccountingService.getSummary()` | **B** | No | Yes | **HIGH** | Account, Transaction, Journal | Journal (C) | Extend InsightsController after foundation in |
| 23 | **Aged Receivables Report** | ❌ Missing | None explicit in source | **C** | No | No | HIGH | Invoice, Customer, Payment | Invoice | Build using existing host Invoice model |
| 24 | **POS System** | ❌ Missing | `CommerceCore/PosController.php`, `PosHeldOrder.php` | **E** | No | No | Low (retail, not service) | — | — | Skip |
| 25 | **Inventory Management** | ❌ Missing | `CommerceCore/InventoryController.php` | **E** | No | No | Low (out of scope) | — | — | Skip |
| 26 | **Returns / Refunds (retail)** | Partial (CreditNote handles service refunds) | `CommerceCore/ReturnController.php`, `SaleReturn.php` | **E** | No | Partial | Low | — | — | Skip; host CreditNote covers service refund scenario |
| 27 | **TitanMoney Mobile App** | ❌ No API | `mobile_apps/TitanMoney/` (Flutter client) | **C** | No | No | Medium | All finance API endpoints | API layer (deferred) | Deferred — needs API layer after finance modules mature |

---

## Summary by Classification

| Class | Count | Features |
|-------|-------|---------|
| **A** — Keep host | 10 | Quotes, Invoices, Payments, Credit Notes, Expenses, Expense Categories, Taxes, Attendance, SaaS Billing, Leave |
| **B** — Enrich host | 3 | BankAccounts (link to CoA), Finance Reporting (extend), Cashflow/P&L (extend) |
| **C** — Extract (new) | 9 | Chart of Accounts, Journal/Ledger, Transactions, Suppliers, Purchases, Payroll, Employees, Asset Accounting, Aged Receivables |
| **D** — Extract logic only | 0 | — |
| **E** — Skip | 4 | POS, Inventory, Retail Returns, SaaS Billing duplicate |

---

## High-Value Extraction Targets (Class C, HIGH value)

1. Chart of Accounts
2. Journal Entries + Journal Lines
3. Transactions / Ledger postings
4. Suppliers
5. Purchases / Procurement
6. Payroll
7. Employee Records
8. Asset Accounting + Depreciation
