# FINANCE_CONNECTION_MAP.md
## Finance Feature → Required Domain Integration Map

**Audit date:** 2026-04-03

This document maps each finance feature (both existing and candidate) to the other host domains
it must connect to. Blockers and deferred dependencies are noted.

---

## 1. Already-Integrated Finance Features (host Money domain)

### 1.1 Quotes
```
Quote
 ├── customer_id          → app/Models/Crm/Customer.php
 ├── company_id           → app/Models/Company.php (BelongsToCompany)
 ├── tax_id               → app/Models/Money/Tax.php
 ├── items                → app/Models/Money/QuoteItem.php
 ├── convertToJob()       → app/Models/Work/ServiceJob.php
 └── convertToInvoice()   → app/Models/Money/Invoice.php
```
**Status:** Fully connected. No blockers.

---

### 1.2 Invoices
```
Invoice
 ├── customer_id          → app/Models/Crm/Customer.php
 ├── company_id           → app/Models/Company.php
 ├── quote_id             → app/Models/Money/Quote.php
 ├── service_job_id       → app/Models/Work/ServiceJob.php
 ├── tax_id               → app/Models/Money/Tax.php
 ├── items                → app/Models/Money/InvoiceItem.php
 ├── payments             → app/Models/Money/Payment.php
 └── creditNotes          → app/Models/Money/CreditNote.php
```
**Status:** Fully connected. No blockers.

---

### 1.3 Payments
```
Payment
 ├── invoice_id           → app/Models/Money/Invoice.php
 ├── company_id           → app/Models/Company.php
 └── bank_account_id      → app/Models/Money/BankAccount.php
```
**Status:** Fully connected.  
**Future connection:** When Chart of Accounts is extracted, payments should post journal entries.

---

### 1.4 Expenses
```
Expense
 ├── expense_category_id  → app/Models/Money/ExpenseCategory.php
 ├── company_id           → app/Models/Company.php
 ├── user_id              → app/Models/User.php
 ├── service_job_id       → app/Models/Work/ServiceJob.php (optional)
 └── approved_by          → app/Models/User.php
```
**Status:** Fully connected.  
**Future connection:** When Journal is extracted, approved expenses should post debit journal entries.

---

## 2. Extraction Candidates — Connection Requirements

### 2.1 Chart of Accounts (`app/Models/Money/Account.php`)
```
Account
 ├── company_id           → app/Models/Company.php  [BelongsToCompany]
 ├── type                 → enum: asset | liability | equity | income | expense
 ├── transactions()       → Transaction (C — to be extracted)
 └── journalLines()       → JournalLine (C — to be extracted)
```
**Required connected domains:**
- `Company` — mandatory tenancy anchor
- `Transaction` (C) — depends on Account; extract together
- `JournalLine` (C) — depends on Account; extract together

**Blocking dependencies:** None. Can be extracted first.  
**Recommended:** Extract Account as the very first step.

---

### 2.2 Journal Entries / Ledger
```
JournalEntry
 ├── company_id           → app/Models/Company.php
 ├── user_id              → app/Models/User.php
 └── lines                → JournalLine

JournalLine
 ├── journal_entry_id     → JournalEntry
 └── account_id           → Account (C) — must exist first
```
**Required connected domains:**
- `Account` (C) — must be extracted before Journal
- `User` — already in host
- `Company` — already in host

**Future auto-posting connections:**
- `Invoice` → posts debit A/R + credit Income on issue
- `Payment` → posts debit Bank + credit A/R on receipt
- `Expense` → posts debit Expense + credit Bank/Payable on approval
- `Purchase` → posts debit Asset/Expense + credit Payable on receipt
- `Payroll` → posts debit Wages + credit Bank + credit Payable deductions

**Blocking dependency:** Account model must be present.

---

### 2.3 Transactions (Ledger Postings)
```
Transaction
 ├── account_id           → Account (C)
 ├── company_id           → app/Models/Company.php
 ├── journal_entry_id     → JournalEntry (C) (optional reference)
 └── reference            → polymorphic (Invoice, Payment, Expense, Purchase, Payroll)
```
**Required connected domains:** Account (C), Company  
**Blocking dependency:** Account model must exist.

---

### 2.4 Suppliers
```
Supplier
 ├── company_id           → app/Models/Company.php
 └── purchases()          → Purchase (C)
```
**Required connected domains:** Company  
**Blocking dependencies:** None. Can be extracted immediately after Account.  
**Future connections:** Purchase orders, Expense allocation (supplier cost), Asset procurement.

---

### 2.5 Purchases / Procurement
```
Purchase
 ├── company_id           → app/Models/Company.php
 ├── supplier_id          → Supplier (C)
 ├── user_id              → app/Models/User.php (created by)
 └── items                → PurchaseItem

PurchaseItem
 ├── purchase_id          → Purchase
 └── asset_id             → Asset (C) (optional — for asset acquisition purchases)
```
**Required connected domains:** Supplier (C), Company, User  
**Future connections:**
- Expense allocation (purchase costs as company expenses)
- Asset creation on receipt of capital items
- Journal posting on purchase receipt (debit Asset/Expense, credit Accounts Payable)

**Blocking dependency:** Supplier must be extracted first.

---

### 2.6 Employee Records
```
Employee
 ├── company_id           → app/Models/Company.php
 ├── user_id              → app/Models/User.php (optional — maps to staff user)
 └── team_id              → app/Models/Team/Team.php (bridge to host teams)
```
**Required connected domains:** Company, User, Team  
**Bridge note:** Host has `TeamMember` for operational staff. `Employee` extends this with HR/salary
data. The integration should bridge `Employee.user_id → User` and optionally bridge to `TeamMember`
rather than replacing it.

**Future connections:**
- Payroll depends on Employee.basic_salary
- Leave deductions feed into Payroll
- Attendance hours feed into Payroll (if hourly rates are implemented)
- Timelog / Timesheet hours can cross-reference Payroll periods

**Blocking dependencies:** None. Can be extracted independently.

---

### 2.7 Payroll
```
Payroll
 ├── company_id           → app/Models/Company.php
 ├── employee_id          → Employee (C)
 ├── period               → month/year string
 ├── basic_salary         → sourced from Employee.basic_salary
 ├── bonus                → manual input
 ├── deduction            → manual input (can link Leave / Timelog)
 └── net_salary           → computed
```
**Required connected domains:**
- `Employee` (C) — mandatory; must be extracted before Payroll
- `Company` — mandatory
- `Work/Attendance` (already in host) — can feed hours for timesheet-based pay
- `Work/Leave` (already in host) — unpaid leave can feed deductions
- `Work/Timelog` (already in host) — for hourly/contract pay computation

**Future connections:**
- Journal posting on payroll approval (debit Wages Expense, credit Bank Account, credit Tax Payable)

**Blocking dependency:** Employee must be extracted first.

---

### 2.8 Asset Accounting + Depreciation
```
Asset (financial)
 ├── company_id           → app/Models/Company.php
 ├── purchase_id          → Purchase (C) (optional — links to procurement)
 ├── purchase_price       → decimal
 ├── current_value        → decimal (updated by depreciation run)
 ├── depreciation_percentage → decimal
 └── status               → active | disposed
```
**Required connected domains:** Company  
**Optional connections:**
- `Purchase` / `PurchaseItem` — asset acquired through procurement
- `Facility/SiteAsset` or `Work/SiteAsset` — physical asset may be cross-referenced (different model, do not merge)
- Journal posting on depreciation run (debit Depreciation Expense, credit Accumulated Depreciation)

**Naming note:** Name the financial asset model `app/Models/Money/FinancialAsset.php` (or
`CapitalAsset.php`) to avoid collision with existing `Facility/SiteAsset.php` and `Work/SiteAsset.php`.

**Blocking dependencies:** None for model creation. Purchase should ideally precede asset acquisition flows.

---

### 2.9 Finance Reporting (Extension of InsightsController)
```
Required data sources:
 ├── Invoice           → already in host
 ├── Payment           → already in host
 ├── Expense           → already in host
 ├── Account           → C (needed for P&L by account)
 ├── Transaction       → C (needed for cashflow statement)
 ├── JournalEntry      → C (needed for trial balance)
 └── Payroll           → C (needed for wage cost reporting)
```
**Required connected domains:** All of the above  
**Recommended timing:** Build reporting extension only after Chart of Accounts, Journal, and
Payroll are in place. Extend `InsightsController` or create a new `FinanceReportController`.

---

### 2.10 Aged Receivables Report
```
Required data sources:
 ├── Invoice          → host
 ├── Payment          → host
 └── Customer (CRM)   → host
```
**No new models needed.** Can be built using existing host data.  
**Implementation:** Add `aged` method to `InsightsController` or a dedicated route in
`money.routes.php`.

---

## 3. Dependency Graph (Extraction Order)

```
Phase 1 — Foundation
 └── Account (Chart of Accounts)

Phase 2 — Ledger
 └── Transaction + JournalEntry + JournalLine
      └── depends on: Account

Phase 3 — Suppliers + Procurement
 ├── Supplier
 └── Purchase + PurchaseItem
      └── depends on: Supplier

Phase 4 — HR + Payroll
 ├── Employee
 └── Payroll
      └── depends on: Employee
      └── connects to: Work/Attendance, Work/Leave, Work/Timelog

Phase 5 — Asset Accounting
 └── FinancialAsset
      └── connects to: Purchase (optional)

Phase 6 — Reporting
 ├── Aged Receivables (no new models — uses Invoice + Customer)
 ├── P&L / Cashflow (extends InsightsController — uses Account + Transaction)
 └── Finance Dashboard

Phase 7 — Auto-posting (Journal Observers)
 └── Wire Invoice, Payment, Expense, Purchase, Payroll to JournalEntry auto-posting
      └── depends on: all Phase 1–4 models
```

---

## 4. Missing Connection Links

| Link | Status | Recommendation |
|------|--------|----------------|
| Payment → JournalEntry auto-post | Missing | Implement in Phase 7 after Journal is in |
| Invoice issue → JournalEntry auto-post | Missing | Phase 7 |
| Expense approval → JournalEntry auto-post | Missing | Phase 7 |
| Purchase receipt → JournalEntry auto-post | Missing | Phase 7 |
| Payroll approval → JournalEntry auto-post | Missing | Phase 7 |
| Depreciation run → JournalEntry auto-post | Missing | Phase 7 |
| ServiceAgreement → recurring invoice trigger | Missing | Deferred (Phase 8) |
| TitanMoney mobile app → API endpoints | Missing | Deferred (after Phase 6) |

---

## 5. Phase 1 Implementation Status (2026-04-03)

### COMPLETED in Finance Pass 2

| Item | Status | File |
|------|--------|------|
| `Account` model | ✅ Done | `app/Models/Money/Account.php` |
| `JournalEntry` model | ✅ Done | `app/Models/Money/JournalEntry.php` |
| `JournalLine` model | ✅ Done | `app/Models/Money/JournalLine.php` |
| Migration 600100 | ✅ Done | `database/migrations/2026_04_03_600100_create_chart_of_accounts_and_journal_tables.php` |
| `AccountingService` | ✅ Done | `app/Services/TitanMoney/AccountingService.php` |
| `AccountController` | ✅ Done | `app/Http/Controllers/Core/Money/AccountController.php` |
| `JournalEntryController` | ✅ Done | `app/Http/Controllers/Core/Money/JournalEntryController.php` |
| `AccountPolicy` | ✅ Done | `app/Policies/AccountPolicy.php` |
| `JournalEntryPolicy` | ✅ Done | `app/Policies/JournalEntryPolicy.php` |
| Routes `money.accounts.*` | ✅ Done | `routes/core/money.routes.php` |
| Routes `money.journal.*` | ✅ Done | `routes/core/money.routes.php` |
| Account CRUD views | ✅ Done | `resources/views/default/panel/user/money/accounts/` |
| Journal CRUD views | ✅ Done | `resources/views/default/panel/user/money/journal/` |
| `ExpenseApproved` event | ✅ Done | `app/Events/Money/ExpenseApproved.php` |
| `PaymentRecorded` event | ✅ Done | `app/Events/Money/PaymentRecorded.php` |
| Auto-post Invoice→Ledger | ✅ Done | `app/Listeners/Money/PostInvoiceIssuedToLedger.php` |
| Auto-post Payment→Ledger | ✅ Done | `app/Listeners/Money/PostPaymentRecordedToLedger.php` |
| Auto-post Expense→Ledger | ✅ Done | `app/Listeners/Money/PostExpenseApprovedToLedger.php` |
| Tests | ✅ Done | `tests/Feature/Money/AccountingTest.php` |

### Auto-Posting Hooks Connected

| Trigger | Event | Listener | Target |
|---------|-------|----------|--------|
| Invoice status → 'issued' | `InvoiceIssued` | `PostInvoiceIssuedToLedger` | DR AR / CR Income |
| Payment store | `PaymentRecorded` | `PostPaymentRecordedToLedger` | DR Bank / CR AR |
| Expense approve | `ExpenseApproved` | `PostExpenseApprovedToLedger` | DR Expense / CR Payable |

### Notes
- Original plan phases 1+2 combined into a single pass (Chart of Accounts + Journal Engine together).
- Auto-posting from Phase 7 brought forward to Phase 1 (minimum viable hooks for Invoice, Payment, Expense).
- Debit/credit balance validation enforced in `AccountingService::assertBalanced()`.
- All entries are idempotent: duplicate posts are silently skipped.
- Default accounts (Bank 1000, AR 1200, Income 4000, Expenses 6000, Payable 2000) are
  auto-created via `firstOrCreate` when a posting occurs without pre-configured accounts.
