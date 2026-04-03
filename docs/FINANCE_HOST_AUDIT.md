# FINANCE_HOST_AUDIT.md
## Finance Features Already Present in the Host Repository

**Audit date:** 2026-04-03  
**Host branch:** `copilot/finance-module-extraction-zero-host-audit`

---

## 1. Overview

The host repository contains a well-established **Money** domain under `app/Models/Money/`,
`app/Http/Controllers/Core/Money/`, and `routes/core/money.routes.php`, plus a separate
**Finance** area that manages platform-level subscription plans and payment gateways.

The following survey covers every finance-relevant area found by scanning:
- `app/Models/`
- `app/Http/Controllers/Core/Money/`
- `app/Services/Finance/` and `app/Services/Money/`
- `app/Policies/`
- `routes/core/money.routes.php`
- `database/migrations/` (filtered for finance)
- `resources/views/default/panel/user/money/`
- `app/Http/Controllers/Core/Insights/InsightsController.php`

---

## 2. Core Finance Domains — Host Status

### 2.1 Quotes
| Item | Status | Path |
|------|--------|------|
| Model | ✅ Complete | `app/Models/Money/Quote.php`, `QuoteItem.php` |
| Template model | ✅ Complete | `app/Models/Money/QuoteTemplate.php`, `QuoteTemplateItem.php` |
| Controller | ✅ Complete | `app/Http/Controllers/Core/Money/QuoteController.php` |
| Service | ✅ Complete | `app/Services/Money/QuoteService.php` |
| Policy | ✅ Complete | `app/Policies/QuotePolicy.php` |
| Routes | ✅ Complete | `routes/core/money.routes.php` (CRUD + status, convert-to-job, convert-to-invoice, template apply) |
| Views | ✅ Complete | `resources/views/default/panel/user/money/quotes/`, `money/quote-templates/` |
| Migrations | ✅ Complete | `2026_03_30_085500_create_quotes_table.php`, `2026_03_30_121000_update_quotes_and_invoices_for_lifecycle.php`, `2026_03_30_123000_create_quote_items_table.php`, `2026_03_30_150000_add_unique_numbers_to_quotes_invoices.php` |

**Maturity:** HIGH — Full lifecycle including status management, item lines, templates, and conversion to jobs and invoices.

---

### 2.2 Invoices
| Item | Status | Path |
|------|--------|------|
| Model | ✅ Complete | `app/Models/Money/Invoice.php`, `InvoiceItem.php` |
| Controller | ✅ Complete | `app/Http/Controllers/Core/Money/InvoiceController.php` |
| Policy | ✅ Complete | `app/Policies/InvoicePolicy.php` |
| Routes | ✅ Complete | `routes/core/money.routes.php` (CRUD + mark-paid, mark-overdue) |
| Views | ✅ Complete | `resources/views/default/panel/user/money/invoices/` |
| Migrations | ✅ Complete | `2026_03_30_085600_create_invoices_table.php`, `2026_03_30_121000`, `2026_03_30_123100_create_invoice_items_table.php` |

**Statuses used:** `draft`, `issued`, `partial`, `paid`, `overdue`, `void`  
**Maturity:** HIGH — Full CRUD with lifecycle transitions.

---

### 2.3 Payments
| Item | Status | Path |
|------|--------|------|
| Model | ✅ Complete | `app/Models/Money/Payment.php` |
| Controller | ✅ Complete | `app/Http/Controllers/Core/Money/PaymentController.php` |
| Policy | ✅ Complete | `app/Policies/PaymentPolicy.php` |
| Routes | ✅ Complete | `routes/core/money.routes.php` (store against invoice, list) |
| Views | ✅ Complete | `resources/views/default/panel/user/money/payments/` |
| Migrations | ✅ Complete | `2026_03_30_121200_create_payments_table.php`, `2026_03_31_100900_add_bank_account_id_to_payments_table.php` |

**Maturity:** MEDIUM-HIGH — Payment recording against invoices with bank account linking; no multi-gateway routing for business payments yet.

---

### 2.4 Credit Notes
| Item | Status | Path |
|------|--------|------|
| Model | ✅ Complete | `app/Models/Money/CreditNote.php`, `CreditNoteItem.php` |
| Controller | ✅ Complete | `app/Http/Controllers/Core/Money/CreditNoteController.php` |
| Routes | ✅ Complete | `routes/core/money.routes.php` (CRUD + apply-invoice) |
| Views | ✅ Complete | `resources/views/default/panel/user/money/credit-notes/` |
| Migrations | ✅ Complete | `2026_03_31_100400_create_credit_notes_table.php` |

**Maturity:** MEDIUM — CRUD + apply-to-invoice. No refund gateway integration yet.

---

### 2.5 Expenses
| Item | Status | Path |
|------|--------|------|
| Model | ✅ Complete | `app/Models/Money/Expense.php`, `ExpenseCategory.php` |
| Controller | ✅ Complete | `app/Http/Controllers/Core/Money/ExpenseController.php`, `ExpenseCategoryController.php` |
| Policy | ✅ Complete | `app/Policies/ExpensePolicy.php` |
| Routes | ✅ Complete | `routes/core/money.routes.php` (CRUD + approve, reject) |
| Views | ✅ Complete | `resources/views/default/panel/user/money/expenses/`, `money/expense-categories/` |
| Migrations | ✅ Complete | `2026_03_30_190600_create_expense_categories_table.php`, `2026_03_30_190700_create_expenses_table.php`, `2026_03_30_201000_add_approval_fields_to_expenses.php`, `2026_03_30_210500_add_status_fields_to_expenses_table.php` |

**Maturity:** HIGH — Full workflow including category management and approval/rejection flow.

---

### 2.6 Taxes
| Item | Status | Path |
|------|--------|------|
| Model | ✅ Complete | `app/Models/Money/Tax.php` |
| Controller | ✅ Complete | `app/Http/Controllers/Core/Money/TaxController.php` |
| Routes | ✅ Complete | `routes/core/money.routes.php` (CRUD + set-default) |
| Views | ✅ Complete | `resources/views/default/panel/user/money/taxes/` |
| Migrations | ✅ Complete | `2026_03_31_100600_create_bank_accounts_and_taxes_table.php` |

**Maturity:** HIGH — Tax rates with default selection; applied during quote/invoice creation.

---

### 2.7 Bank Accounts
| Item | Status | Path |
|------|--------|------|
| Model | ✅ Complete | `app/Models/Money/BankAccount.php` |
| Controller | ✅ Complete | `app/Http/Controllers/Core/Money/BankAccountController.php` |
| Routes | ✅ Complete | `routes/core/money.routes.php` (CRUD + set-default) |
| Views | ✅ Complete | `resources/views/default/panel/user/money/bank-accounts/` |
| Migrations | ✅ Complete | `2026_03_31_100600_create_bank_accounts_and_taxes_table.php`, `2026_03_31_100900_add_bank_account_id_to_payments_table.php` |

**Maturity:** HIGH — Business bank account registry linked to payment records.

---

### 2.8 Finance Reporting / Insights
| Item | Status | Path |
|------|--------|------|
| Controller | ✅ Partial | `app/Http/Controllers/Core/Insights/InsightsController.php` |
| Scope | Multi-domain | Pulls Quote, Invoice, Payment, Expense, ServiceJob, Customer |

The `InsightsController` provides operational overview analytics (job status, invoice aging,
outstanding balance, payment totals, expense totals, revenue vs expenses over 6 months).

**Maturity:** MEDIUM — Operational reporting exists. No dedicated cashflow statement, profit & loss
by period, aged receivables report, or balance sheet.

---

### 2.9 Platform Finance (Subscriptions & Gateways)
| Item | Status | Path |
|------|--------|------|
| Plan model | ✅ Complete | `app/Models/Plan.php` |
| Subscriptions | ✅ Complete | `app/Models/Finance/Subscription.php`, `app/Models/Subscriptions.php` |
| Gateway models | ✅ Complete | `app/Models/Gateways.php`, `GatewayProducts.php`, `GatewayTax.php` |
| Service | ✅ Complete | `app/Services/Finance/PlanService.php` |
| Payment gateways | ✅ Complete | `app/Services/Payment/Gateways/` (multi-gateway) |
| Admin views | ✅ Complete | `resources/views/default/panel/admin/finance/` (plans, gateways) |
| Migrations | ✅ Complete | Many gateway/subscription migrations 2019–2026 |

**Note:** This is the platform's own SaaS subscription billing, distinct from business-level
finance domains. Do not conflate with business invoice/payment flows.  
**Maturity:** HIGH — Mature multi-gateway subscription engine.

---

## 3. Connected Business Domains — Host Status

### 3.1 Customers / CRM
- `app/Models/Crm/Customer.php` — full customer records with contacts, documents, notes
- `app/Models/Crm/Deal.php`, `Enquiry.php` — pipeline and lead management
- Quotes and Invoices reference `customer_id` → host CRM is well connected

### 3.2 Jobs / Work
- `app/Models/Work/ServiceJob.php` — jobs linkable to quotes/invoices
- Quotes convert to jobs (`quotes.convert-job`)
- Expenses can be linked to jobs

### 3.3 Attendance / Leave
- `app/Models/Work/Attendance.php` — check-in/check-out with duration
- `app/Models/Work/Leave.php`, `LeaveQuota.php`, `LeaveHistory.php` — leave management exists

### 3.4 Timesheets
- `app/Models/Work/Timelog.php`, `WeeklyTimesheet.php` — time tracking

### 3.5 Teams / Staff
- `app/Models/Team/Team.php`, `TeamMember.php`, `CleanerProfile.php`

---

## 4. Finance Domains NOT Present in Host

The following finance domains have been confirmed **absent** from the host repository:

| Domain | Notes |
|--------|-------|
| **Chart of Accounts** | No `Account` model, no account type hierarchy |
| **General Ledger / Journal Entries** | No `JournalEntry`, `JournalLine`, or double-entry bookkeeping |
| **Suppliers / Vendors** | No `Supplier` model |
| **Purchase / Procurement** | No `Purchase`, `PurchaseItem` models |
| **Payroll** | No `Payroll` model; host has team members but no salary/payroll engine |
| **Employee Records** | Host has `Team/TeamMember` but no formal `Employee` entity with salary and HR fields |
| **Asset Accounting** | Host has `Facility/SiteAsset` and `Work/SiteAsset` (physical assets), but no asset accounting with purchase price, depreciation, and current-value tracking |
| **Finance Reporting** | No standalone cashflow, P&L, aged receivables, or balance sheet reports |
| **Recurring Business Billing** | Platform has subscription billing for SaaS plans; business-level recurring invoice billing (e.g., service agreements) is absent |

---

## 5. Architecture Notes

- **Tenancy model:** All Money models use `company_id` scoping via the `BelongsToCompany` concern.
- **Route convention:** Money routes live in `routes/core/money.routes.php`, loaded by `RouteServiceProvider::loadCoreRoutes()`. Named route prefix: `dashboard.money.*`.
- **Policy gates:** `InvoicePolicy`, `PaymentPolicy`, `QuotePolicy`, `ExpensePolicy` exist in `app/Policies/`.
- **View resolution:** Money views extend `default.panel.layout.app` under `resources/views/default/panel/user/money/`.
- **Number generation:** Quotes and invoices have both a legacy `number` (NOT NULL) and `quote_number` / `invoice_number` (nullable) columns; new creates must populate `number` until schema refactor completes.
