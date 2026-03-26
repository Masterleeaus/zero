# Route & Controller Merge Plan

## Current state
- Host dashboard routes live in a single `routes/panel.php` group (`auth`, `updateUserActivity`), while public/auth/api routes are split (`routes/web.php`, `routes/auth.php`, `routes/api.php`, `routes/webhooks.php`, `routes/channels.php`, `routes/console.php`).
- WorkCore ships a monolithic `routes/web.php` (prefixed `account`, middleware `auth|multi-company-select|email_verified`) containing all CRM/Work/Money/Team/Support endpoints and blade responses.
- Controller naming overlaps: `DashboardController`, `SettingsController`, `NotificationController`, `SearchController`, `Payment*`, `Invoice*`, `OrderController`, `Gdpr*`, `Attendance*`, `Leave*`.

## Route modularisation target
- Add core route files loaded from `RouteServiceProvider`:
  - `routes/core/crm.routes.php`
  - `routes/core/work.routes.php`
  - `routes/core/money.routes.php`
  - `routes/core/team.routes.php`
  - `routes/core/support.routes.php`
  - `routes/core/insights.routes.php`
- Keep existing `routes/panel.php` intact; mount the new core route files within the authenticated dashboard group to avoid duplicate auth wrappers.
- Remove reliance on WorkCore’s `routes/web.php` monolith once routes are split and namespaced.

## Controller placement & merge rules
- **CRM**: move `Client*`, `Lead*`, `Deal*`, `Proposal*`, `Gdpr*`, `Contact*`, `Category/SubCategory*` into `App\Http\Controllers\Crm`; adjust names to `Customer`, `Enquiry`, `Pipeline` vocabulary.
- **Work**: move `Project*` (sites), `Task*` (service job/checklist), `SubTask*`, `Timelog*`, `GanttLinkController`, `Discussion*`, `Calendar` into `App\Http\Controllers\Work`; split CRUD vs workflow endpoints.
- **Money**: merge `Invoice*`, `Estimate/Quote*`, `CreditNote`, `Payment*`, `Expense*`, `Order*`, `BankAccount*` with existing finance flow (`Finance\GatewayController`, `Finance\PaymentProcessController`). Introduce interfaces if bindings collide.
- **Team**: move `Employee*` (cleaners), `Attendance*`, `Leave*`, `Shift*`, `Promotion*`, `Department/Designation*`, `Award*`, `EmergencyContact*` into `App\Http\Controllers\Teamwork`; keep `team_id` as grouping only.
- **Support**: move `Ticket*`, `Notice*`, `KnowledgeBase*`, `Discussion*` into `App\Http\Controllers\Support`; reuse `Dashboard\SupportController` surfaces where possible.
- **Insights**: move reporting controllers (`IncomeVsExpenseReportController`, `SalesReportController`, `TaskReportController`, `TimelogReportController`, `AttendanceReportController`, `LeadReportController`) into `App\Http\Controllers\Insights`.

## Middleware & binding strategy
- Use host middlewares (`auth`, `verified`/`email_verified`, `updateUserActivity`, locale/theme selectors). Replace WorkCore `multi-company-select` with a host-friendly resolver that selects `company_id` and sets a tenant scope.
- Add explicit route model binding for new models via `RouteServiceProvider`.
- Adopt named routes aligned to menu entries (`crm.customers.*`, `work.sites.*`, `money.invoices.*`, `team.attendance.*`, `support.tickets.*`, `insights.reports.*`) to prevent collisions with existing dashboard names.

## AJAX/API alignment
- Separate data endpoints (DataTables/timelog fetchers/report widgets) into `api.php` where appropriate; keep UI routes in core route files.
- Standardise responses using host helpers (JSON resource/response macros) instead of WorkCore inline arrays.
