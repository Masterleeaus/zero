# FINANCE_INTEGRATION_RISKS.md
## Finance Integration Risk Register

**Audit date:** 2026-04-03

This document catalogues known and anticipated risks for each finance extraction phase.

---

## Risk Classification

| Severity | Meaning |
|----------|---------|
| 🔴 HIGH | Likely to break existing functionality if not addressed |
| 🟡 MEDIUM | May cause problems; requires careful handling |
| 🟢 LOW | Minor; manageable with standard care |

---

## 1. Route Conflicts

### 1.1 `money.*` Route Namespace Collision
**Severity:** 🟡 MEDIUM  
**Risk:** New routes for accounts, journal, suppliers, purchases, payroll, assets all need to be
added to `routes/core/money.routes.php`. Incorrect prefix configuration or duplicate name
declaration will break existing `dashboard.money.quotes.*`, `invoices.*`, etc.  
**Mitigation:** Every new route group must use a unique prefix and named route segment. Add each
phase's routes in a separate block clearly commented. Test with `php artisan route:list` after each addition.

### 1.2 `work.*` Route Namespace (Employee)
**Severity:** 🟡 MEDIUM  
**Risk:** Adding `Employee` routes to `work.routes.php` alongside existing work routes (ServiceJob,
ServicePlan, Attendance, etc.) may cause route name conflicts if not namespaced carefully.  
**Mitigation:** Use `work.employees.*` prefix. Verify `route:list` after adding.

### 1.3 CommerceCore Route Files Must Not Be Imported
**Severity:** 🔴 HIGH  
**Risk:** CommerceCore ships its own `routes/web.php`, `routes/api.php`, and `routes/admin.php`.
If any of these are imported or registered wholesale, they will conflict with host routes and
bypass host auth/middleware.  
**Mitigation:** Never import CommerceCore route files. Extract only model/controller/service logic.

---

## 2. Schema / Migration Collisions

### 2.1 `accounts` Table Name Collision
**Severity:** 🔴 HIGH  
**Risk:** The host already has `social_media_accounts` (via SocialMedia module) and potentially
other unrelated `account` concepts. CommerceCore creates an `accounts` table. The host
`BusinessSuiteAccount` model may use a related table name.  
**Verification required:** Run `SHOW TABLES LIKE '%account%'` before writing the migration.  
**Mitigation:** If `accounts` already exists, use `chart_of_accounts` as the table name. Confirm
the model's `$table` property matches.

### 2.2 `transactions` Table Name Collision
**Severity:** 🔴 HIGH  
**Risk:** Many Laravel/payment packages use a `transactions` table. The host may have one from
Stripe, Paddle, or a payment gateway package.  
**Verification required:** Check `database/migrations/` for any existing `transactions` table.  
**Mitigation:** Name the host table `ledger_transactions` and the model `LedgerTransaction`.

### 2.3 `employees` Table
**Severity:** 🟡 MEDIUM  
**Risk:** Host has `team_members` table. A new `employees` table is additive and should not
conflict, but migration must not assume the table is absent.  
**Mitigation:** Use a `CREATE TABLE IF NOT EXISTS` guard or standard `Schema::create` wrapped in
`Schema::hasTable` check.

### 2.4 `payrolls` Table
**Severity:** 🟢 LOW  
**Risk:** Name is unique; unlikely to collide.  
**Mitigation:** Standard migration creation.

### 2.5 `suppliers` Table
**Severity:** 🟢 LOW  
**Risk:** No existing `suppliers` table detected.  
**Mitigation:** Standard migration creation.

### 2.6 `purchases` / `purchase_items` Tables
**Severity:** 🟢 LOW  
**Risk:** No existing tables with these names detected.  
**Mitigation:** Standard migration creation.

### 2.7 `financial_assets` Table
**Severity:** 🟢 LOW  
**Risk:** `facility_site_assets` and `work_site_assets` already exist but are different concerns.
Name the new table `financial_assets` explicitly.  
**Mitigation:** Name `financial_assets`; document the distinction from physical/facility assets.

### 2.8 CommerceCore Migration Timestamps
**Severity:** 🟡 MEDIUM  
**Risk:** CommerceCore migrations use timestamps in the `2026_03_*` range which overlaps with host
migrations. If any CommerceCore migration file is copied into `database/migrations/` without
renaming, it may run out of order or conflict.  
**Mitigation:** Always create brand-new migration files for host integration; never copy
CommerceCore migration files directly into the host migrations directory.

---

## 3. Model Duplication Risks

### 3.1 `Account` vs `BankAccount`
**Severity:** 🟡 MEDIUM  
**Risk:** Host already has `app/Models/Money/BankAccount.php`. The new Chart-of-Accounts
`Account` model is broader (includes all account types). Naming must be clear.  
**Mitigation:** New model is `app/Models/Money/Account.php` (Chart of Accounts). Existing
`BankAccount` remains. `BankAccount` may eventually gain an optional `account_id` FK to link
to the CoA entry for the bank account — this is a Phase 1 enrichment option.

### 3.2 `Attendance` Duplication
**Severity:** 🔴 HIGH (if not handled)  
**Risk:** CommerceCore has its own `Attendance` model. Host has `Work/Attendance.php`.  
**Mitigation:** Do NOT extract CommerceCore's Attendance model. Use host `Work/Attendance.php`
as the data source for payroll hour inputs. This is documented in the overlap matrix as class A.

### 3.3 `Employee` vs `TeamMember` Duplication
**Severity:** 🟡 MEDIUM  
**Risk:** If `Employee` and `TeamMember` are treated as identical, HR data and operational data
will be collapsed onto a model designed for only one purpose.  
**Mitigation:** Keep both. `Employee` = formal HR record with salary, department, employment dates.
`TeamMember` = operational scheduling/dispatch role. Bridge via `user_id`.

### 3.4 `Expense` Duplication
**Severity:** 🔴 HIGH (if not handled)  
**Risk:** CommerceCore has its own `Expense` model. Host has a richer `Money/Expense.php` with
categories and approval workflow.  
**Mitigation:** Do NOT extract CommerceCore's Expense model. Host version is superior. Documented
as class A in overlap matrix.

### 3.5 `Subscription` Duplication
**Severity:** 🔴 HIGH (if not handled)  
**Risk:** Both host and CommerceCore have `Subscription` models serving completely different
purposes (platform SaaS billing vs. store-level subscription products).  
**Mitigation:** Do NOT extract CommerceCore's Subscription model. Document clearly that host
`app/Models/Finance/Subscription.php` and `app/Models/Subscriptions.php` serve the SaaS
billing layer only.

---

## 4. Tenancy Conflicts

### 4.1 `store_id` vs `company_id`
**Severity:** 🔴 HIGH  
**Risk:** All CommerceCore models use `store_id` and the `BelongsToStore` trait. If any model is
imported without changing to `company_id` + `BelongsToCompany`, multi-tenant data isolation will
be broken.  
**Mitigation:** Every extracted model must:
1. Change `store_id` → `company_id` in `$fillable`
2. Replace `use App\Traits\BelongsToStore` with `use App\Models\Concerns\BelongsToCompany`
3. Remove any global `StoreScope` from the model
4. Verify that all query scopes use `company_id`

### 4.2 Global Store Scope
**Severity:** 🔴 HIGH  
**Risk:** CommerceCore uses `app/Models/Scopes/StoreScope.php` applied globally to many models.
If this scope class is imported, it will silently filter all queries by `store_id` which won't
exist in the host.  
**Mitigation:** Do NOT import or reference `StoreScope`. All scoping is handled by
`BelongsToCompany` in the host.

---

## 5. Auth / Policy Conflicts

### 5.1 CommerceCore Auth Controllers
**Severity:** 🔴 HIGH (if imported)  
**Risk:** CommerceCore ships its own Auth controllers (login, register, password reset, etc.).
Importing these would conflict with the host auth stack.  
**Mitigation:** Do NOT extract any file from `CommerceCore/app/Http/Controllers/Auth/`.

### 5.2 Missing Policies for New Models
**Severity:** 🟡 MEDIUM  
**Risk:** CommerceCore does not ship Laravel policies. Extracted models without policies bypass
authorization checks.  
**Mitigation:** Create a policy for every new extracted model following the pattern of
`app/Policies/InvoicePolicy.php`. Register each in `AuthServiceProvider`.

### 5.3 Role / Permission Assumptions
**Severity:** 🟡 MEDIUM  
**Risk:** CommerceCore uses Spatie permissions (noted in migrations: `2026_03_22_190225_create_permission_tables.php`).
The host may use a different permission system.  
**Mitigation:** Do not extract permission table migrations. Use host's existing role/permission
system for all new finance controllers.

---

## 6. UI / View Duplication Risks

### 6.1 Admin vs User Panel Views
**Severity:** 🟡 MEDIUM  
**Risk:** CommerceCore views are in `resources/views/admin/`. The host convention is
`resources/views/default/panel/user/money/`. Importing CommerceCore views wholesale would create
an orphaned view tree outside the themed layout.  
**Mitigation:** Extract view logic (HTML structure) from CommerceCore views and rebuild as Blade
files extending `default.panel.layout.app`. Do not import CommerceCore layouts, nav, or admin shells.

### 6.2 CSS / JS Asset Conflicts
**Severity:** 🟡 MEDIUM  
**Risk:** CommerceCore views reference Tailwind CSS classes and may use Alpine.js, Livewire, or
custom JS not present in the host.  
**Mitigation:** Adapt views to use the host's existing CSS component system. Check what
Alpine/Livewire/Blade component conventions the host uses before building new views.

---

## 7. Testing Risks

### 7.1 No Tests for Finance Domain in Host
**Severity:** 🟡 MEDIUM  
**Risk:** The existing Money domain controllers (InvoiceController, PaymentController, etc.)
appear to have no dedicated feature tests. New extractions without tests increase regression risk.  
**Mitigation:** For every extracted controller, write a basic feature test covering:
- Index route returns 200
- Store route creates a record
- Policy blocks unauthorized access
  Place tests in `tests/Feature/Money/` following host test conventions.

### 7.2 CommerceCore Tests Not Portable
**Severity:** 🟢 LOW  
**Risk:** CommerceCore has its own test suite in `tests/Feature/` and `tests/Unit/`. These tests
reference `Store`, `BelongsToStore`, and the CommerceCore database structure and cannot be imported.  
**Mitigation:** Write new tests for each extracted feature against the host structure.

---

## 8. Provider / Service Container Risks

### 8.1 AccountingService Not Registered
**Severity:** 🟡 MEDIUM  
**Risk:** The `AccountingService` extracted from CommerceCore is not a singleton and has no
interface contract in the source. If used across multiple call sites, it should be bound.  
**Mitigation:** Either bind `AccountingService` as a singleton in `MoneyServiceProvider` (if one
is created) or in `AppServiceProvider`. Alternatively, inject via constructor DI without binding.

### 8.2 No `MoneyServiceProvider` in Host
**Severity:** 🟢 LOW  
**Risk:** All Money domain bindings are currently implicit. As the domain grows, a dedicated
`MoneyServiceProvider` may be needed to register services, policies, and domain events.  
**Mitigation:** Defer provider creation to Phase 4 or later when multiple services need binding.
Register in `config/app.php` following the pattern of `TitanCoreServiceProvider`.

---

## 9. Data Integrity Risks

### 9.1 Quote `number` Column
**Severity:** 🟡 MEDIUM  
**Risk:** The `quotes` and `invoices` tables have a `number` column that is NOT NULL. New creates
must populate this field until a schema refactor is done to make it nullable.  
**Mitigation:** Any new code touching Quote or Invoice creation must continue to populate `number`.
This applies to any payroll or purchase flow that auto-generates invoices.

### 9.2 Journal Entry Balance Validation
**Severity:** 🟡 MEDIUM  
**Risk:** If journal entry validation (debit total = credit total) is not enforced at the model or
service layer, accounting data integrity will be compromised.  
**Mitigation:** Enforce balance validation in `JournalEntry::saving()` observer and in
`AccountingService::postJournalEntry()`.

### 9.3 Asset Depreciation Idempotency
**Severity:** 🟡 MEDIUM  
**Risk:** Running a depreciation command multiple times for the same period would compound the
depreciation.  
**Mitigation:** Depreciation runs must be idempotent — track processed periods in a
`depreciation_runs` table or check a `last_depreciated_at` column on `FinancialAsset`.

---

## 10. Summary Risk Matrix

| Risk | Severity | Phase |
|------|----------|-------|
| CommerceCore route files imported | 🔴 HIGH | All phases |
| `store_id` tenancy not updated | 🔴 HIGH | All phases |
| `StoreScope` imported | 🔴 HIGH | All phases |
| `accounts` table name collision | 🔴 HIGH | Phase 1 |
| `transactions` table name collision | 🔴 HIGH | Phase 2 |
| Expense model duplicated | 🔴 HIGH | All phases |
| Subscription model duplicated | 🔴 HIGH | All phases |
| Auth controllers imported | 🔴 HIGH | All phases |
| Route name collision in money.routes.php | 🟡 MEDIUM | Each phase |
| Missing policies for new models | 🟡 MEDIUM | Each phase |
| Employee vs TeamMember conflated | 🟡 MEDIUM | Phase 4 |
| CommerceCore views used as-is | 🟡 MEDIUM | Each phase |
| Missing tests | 🟡 MEDIUM | Each phase |
| Journal balance not enforced | 🟡 MEDIUM | Phase 2 |
| Depreciation run not idempotent | 🟡 MEDIUM | Phase 5 |
| AccountingService not bound | 🟢 LOW | Phase 2 |
| CommerceCore migration timestamps | 🟡 MEDIUM | Each phase |

---

## 11. Phase 1 Risk Resolution (2026-04-03)

| Risk | Status |
|------|--------|
| `accounts` table name collision | ✅ RESOLVED — `accounts` table created fresh, no existing table. |
| `store_id` tenancy not updated | ✅ RESOLVED — all new models use `BelongsToCompany` / `company_id`. No `store_id` introduced. |
| Route name collision | ✅ RESOLVED — `money.accounts.*` and `money.journal.*` added cleanly with no collision. |
| Missing policies for new models | ✅ RESOLVED — `AccountPolicy` + `JournalEntryPolicy` created and registered. |
| Missing tests | ✅ RESOLVED — `tests/Feature/Money/AccountingTest.php` covers accounts, journal, posting, tenancy isolation, event registration. |
| Journal balance not enforced | ✅ RESOLVED — `AccountingService::assertBalanced()` throws `InvalidArgumentException` on imbalance. |
| AccountingService not bound | ✅ RESOLVED — Laravel auto-resolution via constructor injection. No extra binding required. |
| Expense model duplicated | ✅ RESOLVED — host `Expense` model used directly. No duplicate created. |
| Auth controllers imported | ✅ RESOLVED — no standalone auth imported. |
| CommerceCore route files imported | ✅ RESOLVED — only accounting core extracted; no CommerceCore route files imported. |

### Remaining risks for Phase 2+
- `transactions` table name collision risk (Phase 2) — renamed to `LedgerTransaction` if needed.
- Employee vs TeamMember conflation (Phase 4) — deferred.
- Depreciation run idempotency (Phase 5) — deferred.
