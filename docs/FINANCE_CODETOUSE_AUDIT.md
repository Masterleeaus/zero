# FINANCE_CODETOUSE_AUDIT.md
## Finance-Related Modules in CodeToUse/ — Source Inventory

**Audit date:** 2026-04-03  
**Primary source:** `CodeToUse/aicore/AICores/CommerceCore-main/`  
**Secondary sources:** `CodeToUse/work/Taskly/`, `CodeToUse/work/WorkOrders/`, `mobile_apps/TitanMoney/`

---

## 1. Primary Source: CommerceCore-main

`CodeToUse/aicore/AICores/CommerceCore-main/` is a **standalone Laravel e-commerce/ERP application**.
It is **not** already integrated into the host. It ships with its own auth, routes, migrations,
models, controllers, services, and views. It uses a `store_id`-based tenancy via the
`BelongsToStore` trait.

**Key difference from host:** CommerceCore uses `store_id` for tenancy; the host uses `company_id`.
Any extracted code must have tenancy references updated from `store_id` → `company_id` and the
`BelongsToStore` trait replaced with the host's `BelongsToCompany` concern.

---

## 2. Finance Domains Found in CommerceCore-main

### 2.1 Chart of Accounts
| Item | Path |
|------|------|
| Model | `app/Models/Account.php` |
| Controller | `app/Http/Controllers/Admin/AccountController.php` |
| Migration (initial) | `database/migrations/2026_03_22_192642_create_accounts_table.php` |
| Migration (enrichment) | `database/migrations/2026_03_23_174711_update_accounts_table_for_chart_of_accounts.php` |
| Views | `resources/views/admin/accounts/` |

**Fields:** `name`, `type`, `balance`, `account_number`, `bank_name`, `is_active`, `store_id`  
**Relationships:** `hasMany(Transaction)`, `hasMany(JournalLine)`  
**Notes:** Account types (`asset`, `liability`, `equity`, `income`, `expense`) added in the second
migration. This is a full chart-of-accounts structure, not just a bank account register.

---

### 2.2 Transactions / Ledger
| Item | Path |
|------|------|
| Model | `app/Models/Transaction.php` |
| Service | `app/Services/AccountingService.php` |
| Controller | `app/Http/Controllers/Admin/TransactionController.php` |
| Migration | `database/migrations/2026_03_22_192642_create_accounts_table.php` (transactions included) |
| Views | `resources/views/admin/transactions/` |

**`AccountingService` capabilities:**
- `recordTransaction()` — posts income/expense/transfer against an account, updates balance
- `transferFunds()` — double-entry transfer between accounts
- `getSummary()` — income, expense, net profit for a date range

**Notes:** Single-entry accounting layer. Not a full double-entry ledger.

---

### 2.3 Journal Entries (Double-Entry)
| Item | Path |
|------|------|
| Model | `app/Models/JournalEntry.php`, `app/Models/JournalLine.php` |
| Controller | `app/Http/Controllers/Admin/JournalEntryController.php` |
| Migration | `database/migrations/2026_03_23_174624_create_journal_entries_table.php`, `2026_03_23_174625_create_journal_lines_table.php` |
| Views | `resources/views/admin/journal/` |

**`JournalEntry` fields:** `store_id`, `reference`, `date`, `description`, `user_id`  
**`JournalLine` fields:** `journal_entry_id`, `account_id`, `debit`, `credit`, `description`  
**Computed:** `getTotalDebitAttribute()`, `getTotalCreditAttribute()`

**Notes:** Full double-entry bookkeeping with balanced debit/credit journal lines. This is the most
valuable accounting primitive not present in the host.

---

### 2.4 Payroll
| Item | Path |
|------|------|
| Model | `app/Models/Payroll.php` |
| Controller | `app/Http/Controllers/Admin/PayrollController.php` |
| Migration | `database/migrations/2026_03_22_192928_create_payrolls_table.php` |
| Views | `resources/views/admin/payroll/` |

**Fields:** `employee_id`, `month`, `basic_salary`, `bonus`, `deduction`, `net_salary`, `status`, `paid_at`  
**Logic:** Generates payroll slip per employee/month; prevents duplicate generation for same period;
supports mark-as-paid.

**Dependencies:** Requires `Employee` model and records.

---

### 2.5 Employees
| Item | Path |
|------|------|
| Model | `app/Models/Employee.php` |
| Controller | `app/Http/Controllers/Admin/EmployeeController.php` |
| Migration | `database/migrations/2026_03_22_192925_create_employees_table.php` |
| Views | `resources/views/admin/employees/` |

**Notes:** The host has `Team/TeamMember` but no formal `Employee` entity with `basic_salary`,
`department`, and HR metadata needed for payroll. This is a genuine gap.

**Dependency:** Payroll depends on Employee.

---

### 2.6 Leave Management
| Item | Path |
|------|------|
| Model | `app/Models/LeaveRequest.php` |
| Controller | `app/Http/Controllers/Admin/LeaveController.php` |
| Migration | `database/migrations/2026_03_22_192929_create_leave_requests_table.php` |
| Views | `resources/views/admin/leaves/` |

**Notes:** Host already has `Work/Leave.php`, `LeaveQuota.php`, `LeaveHistory.php`.
The source version is employee-centric while the host version is worker/cleaner-centric.
Integration value is LOW unless payroll is adopted.

---

### 2.7 Attendance (Source)
| Item | Path |
|------|------|
| Model | `app/Models/Attendance.php` |
| Controller | `app/Http/Controllers/Admin/AttendanceController.php` |
| Migration | `database/migrations/2026_03_22_192926_create_attendances_table.php` |
| Views | `resources/views/admin/attendance/` |

**Notes:** Host already has a well-developed `Work/Attendance.php` with BelongsToCompany, check-in/
check-out, duration calculation, and shift linking.  
**Classification: DUPLICATE — skip.**

---

### 2.8 Assets + Depreciation
| Item | Path |
|------|------|
| Model | `app/Models/Asset.php` |
| Controller | `app/Http/Controllers/Admin/AssetController.php` |
| Migration | `database/migrations/2026_03_22_192643_create_assets_table.php` |
| Views | `resources/views/admin/assets/` |

**Fields:** `name`, `purchase_price`, `current_value`, `purchase_date`, `depreciation_percentage`, `status`, `notes`  

**Notes:** Host has `Facility/SiteAsset.php` and `Work/SiteAsset.php` which track physical assets
at a site/equipment level. Neither includes purchase price, current value, or depreciation.
This source model covers financial asset accounting — a genuine gap.

---

### 2.9 Suppliers / Vendors
| Item | Path |
|------|------|
| Model | `app/Models/Supplier.php` |
| Controller | `app/Http/Controllers/Admin/SupplierController.php` |
| Migration | `database/migrations/2026_03_23_000001_create_suppliers_table.php` |
| Views | `resources/views/admin/suppliers/` |

**Fields:** `name`, `email`, `phone`, `company`, `address`, `status`  
**Relationship:** `hasMany(Purchase)`  
**Notes:** No equivalent in the host. Prerequisite for purchase/procurement flows.

---

### 2.10 Purchases / Procurement
| Item | Path |
|------|------|
| Model | `app/Models/Purchase.php`, `PurchaseItem.php` |
| Controller | `app/Http/Controllers/Admin/PurchaseController.php` |
| Service | `app/Services/PurchaseService.php` |
| Migrations | `2026_03_22_202654_create_purchases_table.php`, `2026_03_22_202700_create_purchase_items_table.php`, `2026_03_23_000002_add_supplier_id_to_purchases_table.php` |
| Views | `resources/views/admin/purchases/` |

**Notes:** Full purchase order flow including supplier linking, item lines, and status tracking.
No equivalent in the host.

---

### 2.11 Finance Reporting / Accounting Reports
| Item | Path |
|------|------|
| Controller | `app/Http/Controllers/Admin/AccountingReportController.php` |
| Service | `app/Services/AccountingService.php` |
| Views | `resources/views/admin/reports/` |

**Capabilities:**
- P&L summary (income, expenses, net profit) for date ranges
- Transaction listing by account/type
- (No aging receivables or balance sheet yet)

**Notes:** The host's `InsightsController` partially covers this but from a job/service operations
perspective. The source's accounting reports focus on accounting accounts and transaction flows.
Integration value: MEDIUM — bridges reporting gap once journal/accounts are integrated.

---

### 2.12 Invoices (Source)
| Item | Path |
|------|------|
| Model | none (uses `Order` with invoice semantics) |
| Controller | `app/Http/Controllers/Admin/InvoiceController.php` |
| Views | `resources/views/admin/invoices/` |

**Notes:** The CommerceCore invoice system is primarily order-to-invoice in a retail/e-commerce
context. The host has a full service-business invoice system with lifecycle states.
**Classification: DUPLICATE — host system is superior. Skip.**

---

### 2.13 Billing (Source)
| Item | Path |
|------|------|
| Controller | `app/Http/Controllers/Admin/BillingController.php` |
| Views | `resources/views/admin/billing/` |

**Notes:** Source billing manages SaaS plan subscriptions for the CommerceCore product. Host already
has a complete platform subscription/gateway system.  
**Classification: DUPLICATE — skip.**

---

### 2.14 Expenses (Source)
| Item | Path |
|------|------|
| Model | `app/Models/Expense.php` |
| Controller | `app/Http/Controllers/Admin/ExpenseController.php` |
| Migration | `database/migrations/2026_03_22_194919_create_expenses_table.php` |
| Views | `resources/views/admin/expenses/` |

**Notes:** Host already has a mature expense system including categories, approval workflow,
job assignment, and status transitions.
**Classification: DUPLICATE — host system is superior. Skip.**

---

## 3. Secondary Sources

### 3.1 Taskly (`CodeToUse/work/Taskly/`)
Contains project-tracking views including an `invoice/` directory. This is client-facing project
billing, not a general finance module. Low extraction value for the current scope.

### 3.2 WorkOrders (`CodeToUse/work/WorkOrders/`)
Contains `service_part` views and a costing/billing dimension for work orders. Relevant to
connecting job costs to invoices but does not constitute a standalone finance module.

### 3.3 TitanMoney (`mobile_apps/TitanMoney/`)
Flutter mobile app providing a money-management interface. This is a **mobile client**, not a
backend finance module. It consumes APIs. The relevant backend API layer does not yet exist in
the host and would need to be built once the finance modules mature.

**Dependencies:** Requires host API endpoints for invoices, payments, expenses, and reports.

### 3.4 Odoo (`CodeToUse/Odoo/`)
Contains an FSM integration note (`FSM.txt`). No finance modules.

---

## 4. Infrastructure Elements to Discard

The following CommerceCore infrastructure **must not** be imported wholesale:

| Element | Reason |
|---------|--------|
| `Auth/` controllers | Host has its own auth stack |
| `RouteServiceProvider` | Host has its own route loading convention |
| `Store` model + `BelongsToStore` trait | Host uses `Company` + `BelongsToCompany` |
| Gateway/subscription billing | Host has a mature multi-gateway platform subscription engine |
| `User` model | Host User model is canonical |
| `app/Providers/` | Host providers must not be overwritten |
| Front-end/storefront routes | Not applicable to service-business context |
| POS system | Not a service-business finance concern in current scope |
| Inventory management | Not in current finance extraction scope |
