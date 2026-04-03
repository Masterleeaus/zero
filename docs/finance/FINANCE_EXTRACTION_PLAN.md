# FINANCE_EXTRACTION_PLAN.md
## Prioritised Finance Integration Plan

**Audit date:** 2026-04-03  
**Status:** Phase 1 (Chart of Accounts + Journal Engine) — **COMPLETE** as of 2026-04-03  
**Phase 2 (Suppliers + Purchases) — PENDING**

---

## Governing Principles

1. **Preserve host architecture.** All extracted code must use `company_id` tenancy and
   `BelongsToCompany`, not `store_id` / `BelongsToStore`.
2. **Extend, don't replace.** Every new model, controller, route, and view must follow host
   conventions and not duplicate existing Money domain files.
3. **Additive migrations only.** Never drop or destructively alter existing host schema.
4. **Test before advancing to next phase.** Each phase must boot and route correctly before
   the next phase begins.
5. **Document every extracted file.** Maintain a source-to-host mapping.

---

## Phase 1 — Chart of Accounts Foundation
**Priority: HIGHEST — blocks everything else**

### What to build
| Item | Source | Host target |
|------|--------|------------|
| `Account` model | `CommerceCore/app/Models/Account.php` | `app/Models/Money/Account.php` |
| `AccountController` | `CommerceCore/Admin/AccountController.php` | `app/Http/Controllers/Core/Money/AccountController.php` |
| Migration | `2026_03_22_192642_create_accounts_table.php` + `2026_03_23_174711_update_accounts_table_for_chart_of_accounts.php` | New migration `create_chart_of_accounts_table.php` |
| Routes | (none) | Add to `routes/core/money.routes.php` under `money.accounts.*` |
| Views | `resources/views/admin/accounts/` | `resources/views/default/panel/user/money/accounts/` |
| Policy | (none in source) | `app/Policies/AccountPolicy.php` |

### Adaptation notes
- Replace `store_id` with `company_id` throughout
- Remove `use App\Traits\BelongsToStore;` → use `app/Models/Concerns/BelongsToCompany.php`
- Account types: `asset`, `liability`, `equity`, `income`, `expense` (same as source)
- Seed a minimal default chart of accounts in seeder or migration (Assets, Liabilities, Equity,
  Income, Cost of Goods Sold, Operating Expenses, Wages)

### Exit criteria
- `Account` CRUD works in UI
- company_id scoping verified
- Default chart seeded

---

## Phase 2 — Journal Entries + Transaction Ledger
**Priority: HIGH — required for proper accounting**

### What to build
| Item | Source | Host target |
|------|--------|------------|
| `JournalEntry` model | `CommerceCore/app/Models/JournalEntry.php` | `app/Models/Money/JournalEntry.php` |
| `JournalLine` model | `CommerceCore/app/Models/JournalLine.php` | `app/Models/Money/JournalLine.php` |
| `Transaction` model | `CommerceCore/app/Models/Transaction.php` | `app/Models/Money/LedgerTransaction.php` (rename to avoid collision with platform usage) |
| `AccountingService` | `CommerceCore/app/Services/AccountingService.php` | `app/Services/Money/AccountingService.php` |
| `JournalEntryController` | `CommerceCore/Admin/JournalEntryController.php` | `app/Http/Controllers/Core/Money/JournalEntryController.php` |
| Migrations | Source migrations | New host migrations (additive) |
| Routes | (none) | Add to `routes/core/money.routes.php` under `money.journal.*` |
| Views | `resources/views/admin/journal/` | `resources/views/default/panel/user/money/journal/` |

### Adaptation notes
- `Transaction` renamed to `LedgerTransaction` to avoid collision with any future payment
  transaction reference
- `AccountingService` adapted to use `company_id` instead of `store_id`
- `JournalEntry` validation: debit total must equal credit total (balanced entry)
- Do NOT wire auto-posting observers in this phase — wire manually via `AccountingService`

### Exit criteria
- Manual journal entry creation works
- Balanced validation enforced
- Account balances update correctly via transactions

---

## Phase 3 — Suppliers + Purchases
**Priority: HIGH — enables procurement and cost tracking**

### What to build
| Item | Source | Host target |
|------|--------|------------|
| `Supplier` model | `CommerceCore/app/Models/Supplier.php` | `app/Models/Money/Supplier.php` |
| `SupplierController` | `CommerceCore/Admin/SupplierController.php` | `app/Http/Controllers/Core/Money/SupplierController.php` |
| `Purchase` model | `CommerceCore/app/Models/Purchase.php` | `app/Models/Money/Purchase.php` |
| `PurchaseItem` model | `CommerceCore/app/Models/PurchaseItem.php` | `app/Models/Money/PurchaseItem.php` |
| `PurchaseController` | `CommerceCore/Admin/PurchaseController.php` | `app/Http/Controllers/Core/Money/PurchaseController.php` |
| `PurchaseService` | `CommerceCore/app/Services/PurchaseService.php` | `app/Services/Money/PurchaseService.php` |
| Migrations | Source | New host migrations |
| Routes | (none) | Add to `routes/core/money.routes.php` |
| Views | `resources/views/admin/suppliers/`, `admin/purchases/` | Host money views |
| Policy | (none in source) | `app/Policies/PurchasePolicy.php`, `SupplierPolicy.php` |

### Adaptation notes
- All models use `company_id` + `BelongsToCompany`
- Purchase status should align with host conventions (draft, ordered, received, cancelled)
- Link purchase receipt to expense allocation or asset creation (deferred to Phase 5)

### Exit criteria
- Supplier CRUD works
- Purchase order creation works with item lines
- Purchase total calculates correctly

---

## Phase 4 — Employee Records + Payroll
**Priority: HIGH — significant operational gap**

### What to build
| Item | Source | Host target |
|------|--------|------------|
| `Employee` model | `CommerceCore/app/Models/Employee.php` | `app/Models/Work/Employee.php` (Work domain — HR) |
| `EmployeeController` | `CommerceCore/Admin/EmployeeController.php` | `app/Http/Controllers/Core/Work/EmployeeController.php` |
| `Payroll` model | `CommerceCore/app/Models/Payroll.php` | `app/Models/Money/Payroll.php` |
| `PayrollController` | `CommerceCore/Admin/PayrollController.php` | `app/Http/Controllers/Core/Money/PayrollController.php` |
| Migrations | Source | New host migrations |
| Routes | (none) | Employee routes in `work.routes.php`; Payroll routes in `money.routes.php` |
| Views | `resources/views/admin/employees/`, `admin/payroll/` | Host views |

### Adaptation notes
- `Employee` is placed in `Work` domain (HR) since it's workforce, not purely financial
- `Employee` bridges to `User` via `user_id` and optionally to `TeamMember` via `team_member_id`
- `Payroll` is placed in `Money` domain
- Payroll deductions can reference `Work/Leave` (unpaid leave days)
- Payroll hours can reference `Work/Timelog` (for timesheet-based pay)
- Do NOT replace `Work/Attendance`, `Work/Leave`, `Work/Timelog` — bridge to them

### Exit criteria
- Employee records can be created with salary information
- Payroll slip generation works per employee/period
- Duplicate payroll generation for same period is blocked

---

## Phase 5 — Asset Accounting + Depreciation
**Priority: MEDIUM — important for capital asset management**

### What to build
| Item | Source | Host target |
|------|--------|------------|
| `FinancialAsset` model | `CommerceCore/app/Models/Asset.php` | `app/Models/Money/FinancialAsset.php` |
| `FinancialAssetController` | `CommerceCore/Admin/AssetController.php` | `app/Http/Controllers/Core/Money/FinancialAssetController.php` |
| Depreciation command | (none in source — build new) | `app/Console/Commands/Money/RunDepreciationCommand.php` |
| Migrations | Source | New host migrations |
| Routes | (none) | Add to `routes/core/money.routes.php` |
| Views | `resources/views/admin/assets/` | Host views |

### Adaptation notes
- Named `FinancialAsset` to avoid collision with `Facility/SiteAsset.php` and `Work/SiteAsset.php`
- Monthly depreciation: scheduled command to reduce `current_value` by `depreciation_percentage`
- Optional: link to Purchase (where the asset was acquired)
- Journal auto-posting for depreciation deferred to Phase 7

### Exit criteria
- Capital asset records can be created and managed
- Depreciation percentage is stored and can be applied manually

---

## Phase 6 — Finance Reporting Extensions
**Priority: MEDIUM — consolidates finance intelligence**

### What to build
| Item | Description | Target |
|------|-------------|--------|
| Aged Receivables | Query `Invoice` where `due_date < today` and `status != paid` | Extend `InsightsController` or new `FinanceReportController` |
| P&L Report | Income vs expenses by period (Account + Transaction) | Extend or new controller |
| Cashflow Statement | Cash in (payments received) vs cash out (expenses + payroll) | Extend or new controller |
| Balance Sheet | Assets = Liabilities + Equity (via Account balances) | Extend or new controller |

### Dependency
- Phase 1–4 must be complete before full P&L and balance sheet work
- Aged receivables can be built immediately (uses existing Invoice/Customer)

### Exit criteria
- Aged receivables report shows correct overdue invoices with aging buckets
- P&L summary report shows income and expense totals

---

## Phase 7 — Journal Auto-Posting (Observers)
**Priority: LOW — advanced accounting automation**

Wire Eloquent observers/listeners to auto-create journal entries when:

| Trigger | Journal Entry |
|---------|--------------|
| Invoice `issued` | Dr: Accounts Receivable / Cr: Income |
| Payment `recorded` | Dr: Bank Account / Cr: Accounts Receivable |
| Expense `approved` | Dr: Expense Account / Cr: Bank Account or Accounts Payable |
| Purchase `received` | Dr: Asset or Expense / Cr: Accounts Payable |
| Payroll `approved` | Dr: Wages Expense / Cr: Bank Account + Cr: Tax Payable |
| Asset depreciation | Dr: Depreciation Expense / Cr: Accumulated Depreciation |

### Dependency
- All Phases 1–5 must be complete and tested
- Default chart of accounts must be seeded with standard account codes

---

## Phase 8 — Deferred / Future Work
| Feature | Reason deferred |
|---------|----------------|
| Recurring business billing | Requires ServiceAgreement → Invoice automation flow |
| TitanMoney mobile API layer | Requires all finance API endpoints to be stable |
| Inventory management | Out of scope for this extraction pass |
| POS system | Not applicable to service-business model |
| Retail returns/refunds | Host CreditNote covers service-business refund scenario |
| Multi-currency | Not in source; requires architectural decision |

---

## Source-to-Host File Mapping (Extraction Candidates)

| Source File | Host Target | Phase |
|-------------|------------|-------|
| `CommerceCore/app/Models/Account.php` | `app/Models/Money/Account.php` | 1 |
| `CommerceCore/Admin/AccountController.php` | `app/Http/Controllers/Core/Money/AccountController.php` | 1 |
| `CommerceCore/app/Models/JournalEntry.php` | `app/Models/Money/JournalEntry.php` | 2 |
| `CommerceCore/app/Models/JournalLine.php` | `app/Models/Money/JournalLine.php` | 2 |
| `CommerceCore/app/Models/Transaction.php` | `app/Models/Money/LedgerTransaction.php` | 2 |
| `CommerceCore/app/Services/AccountingService.php` | `app/Services/Money/AccountingService.php` | 2 |
| `CommerceCore/Admin/JournalEntryController.php` | `app/Http/Controllers/Core/Money/JournalEntryController.php` | 2 |
| `CommerceCore/app/Models/Supplier.php` | `app/Models/Money/Supplier.php` | 3 |
| `CommerceCore/Admin/SupplierController.php` | `app/Http/Controllers/Core/Money/SupplierController.php` | 3 |
| `CommerceCore/app/Models/Purchase.php` | `app/Models/Money/Purchase.php` | 3 |
| `CommerceCore/app/Models/PurchaseItem.php` | `app/Models/Money/PurchaseItem.php` | 3 |
| `CommerceCore/Admin/PurchaseController.php` | `app/Http/Controllers/Core/Money/PurchaseController.php` | 3 |
| `CommerceCore/app/Services/PurchaseService.php` | `app/Services/Money/PurchaseService.php` | 3 |
| `CommerceCore/app/Models/Employee.php` | `app/Models/Work/Employee.php` | 4 |
| `CommerceCore/Admin/EmployeeController.php` | `app/Http/Controllers/Core/Work/EmployeeController.php` | 4 |
| `CommerceCore/app/Models/Payroll.php` | `app/Models/Money/Payroll.php` | 4 |
| `CommerceCore/Admin/PayrollController.php` | `app/Http/Controllers/Core/Money/PayrollController.php` | 4 |
| `CommerceCore/app/Models/Asset.php` | `app/Models/Money/FinancialAsset.php` | 5 |
| `CommerceCore/Admin/AssetController.php` | `app/Http/Controllers/Core/Money/FinancialAssetController.php` | 5 |
| `CommerceCore/app/Services/AccountingService.getSummary()` | Extend `app/Http/Controllers/Core/Insights/InsightsController.php` | 6 |
