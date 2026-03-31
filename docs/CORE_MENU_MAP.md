# WorkCore Cleaning Vertical Menu Map

Target cleaning vertical navigation and the routes/guards used in `MenuService`.

## Menu → Route → Guard

- **Customers** → `dashboard.crm.customers.index` → `Route::has(...)`
- **Enquiries** → `dashboard.crm.enquiries.index` → `Route::has(...)`
- **Follow-Ups** → `dashboard.crm.enquiries.index?status=follow-up` → `Route::has(...)`
- **Jobs** → `dashboard.work.service-jobs.index` → `Route::has(...)`
- **Schedule & Dispatch** → `dashboard.work.shifts.index` → `Route::has(...)`
- **Cleaning Checklist** → `dashboard.work.checklists.index` → `Route::has(...)`
- **Team** (collapse)
  - Cleaners → `dashboard.team.roster.index` → `Route::has(...)`
  - Availability → `dashboard.work.attendance.index` → `Route::has(...)`
  - Shift Log → `dashboard.work.timelogs.index` → `Route::has(...)`
  - Leave → `dashboard.work.leaves.index` → `Route::has(...)`
  - Roles → `dashboard.team.roles.index` → `Route::has(...)`
  - Teams → `dashboard.team.teams.index` → `Route::has(...)`
  - Zones → `dashboard.team.zones.index` → `Route::has(...)`
- **Money** (collapse)
  - Quotes → `dashboard.money.quotes.index` → `Route::has(...)`
  - Invoices → `dashboard.money.invoices.index` → `Route::has(...)`
  - Payments → `dashboard.money.payments.index` → `Route::has(...)`
  - Credit Notes → `dashboard.money.credit-notes.index` → `workcore_feature('credit_notes') && Route::has(...)`
  - Expenses → `dashboard.money.expenses.index` → `Route::has(...)`
  - Bank Accounts → `dashboard.money.bank-accounts.index` → `Route::has(...)`
  - Expense Categories → `dashboard.money.expense-categories.index` → `Route::has(...)`
- **Service Requests** → `dashboard.service-requests.index` → `Route::has(...)`
- **Playbooks** → `dashboard.playbooks.index` → `Route::has(...)`
- **Insights** → `dashboard.insights.overview` → `Route::has(...)`

All labels use `workcore_label('key', __('Fallback'))` so vertical terminology can be overridden via `config/workcore.php`.
