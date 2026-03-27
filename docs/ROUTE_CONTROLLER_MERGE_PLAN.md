# Route & Controller Merge Plan

## Current state
- Host dashboard routes live in `routes/panel.php` plus modular core route files in `routes/core/*.routes.php` loaded via `RouteServiceProvider::loadCoreRoutes()` (CRM, Work, Money, Team, Support, Insights). Public/auth/api routes are still split (`routes/web.php`, `routes/auth.php`, `routes/api.php`, `routes/webhooks.php`, `routes/channels.php`, `routes/console.php`).
- WorkCore ships a monolithic `routes/web.php` (prefixed `account`, middleware `auth|multi-company-select|email_verified`) containing all CRM/Work/Money/Team/Support endpoints and blade responses.
- Controller naming overlaps: `DashboardController`, `SettingsController`, `NotificationController`, `SearchController`, `Payment*`, `Invoice*`, `OrderController`, `Gdpr*`, `Attendance*`, `Leave*`.

## Route modularisation target
- Core route files now exist and are loaded from `RouteServiceProvider`:
  - `routes/core/crm.routes.php` (customers/enquiries routes registered)
  - `routes/core/work.routes.php` (sites/service jobs/checklists routes registered)
  - `routes/core/money.routes.php` (quotes/invoices routes registered)
  - `routes/core/team.routes.php` (team roster wired to host TeamController)
  - `routes/core/support.routes.php` (placeholder, to be filled next)
  - `routes/core/insights.routes.php` (overview/reports routes registered)
- Keep existing `routes/panel.php` intact; mount the new core route files within the authenticated dashboard group to avoid duplicate auth wrappers.
- Remove reliance on WorkCore’s `routes/web.php` monolith once routes are split and namespaced.

## Controller placement & merge rules
- **CRM**: move `Client*`, `Lead*`, `Deal*`, `Proposal*`, `Gdpr*`, `Contact*`, `Category/SubCategory*` into `App\Http\Controllers\Crm`; adjust names to `Customer`, `Enquiry`, `Pipeline` vocabulary. Placeholder controllers exist under `App\Http\Controllers\Core\Crm`.
- **Work**: move `Project*` (sites), `Task*` (service job/checklist), `SubTask*`, `Timelog*`, `GanttLinkController`, `Discussion*`, `Calendar` into `App\Http\Controllers\Work`; split CRUD vs workflow endpoints. Placeholder controllers exist under `App\Http\Controllers\Core\Work`.
- **Money**: merge `Invoice*`, `Estimate/Quote*`, `CreditNote`, `Payment*`, `Expense*`, `Order*`, `BankAccount*` with existing finance flow (`Finance\GatewayController`, `Finance\PaymentProcessController`). Introduce interfaces if bindings collide. Placeholder controllers exist under `App\Http\Controllers\Core\Money`.
- **Team**: move `Employee*` (cleaners), `Attendance*`, `Leave*`, `Shift*`, `Promotion*`, `Department/Designation*`, `Award*`, `EmergencyContact*` into `App\Http\Controllers\Teamwork`; keep `team_id` as grouping only. Core route currently points to host `App\Http\Controllers\Team\TeamController`.
- **Support**: move `Ticket*`, `Notice*`, `KnowledgeBase*`, `Discussion*` into `App\Http\Controllers\Support`; reuse `Dashboard\SupportController` surfaces where possible. Routes pending.
- **Insights**: move reporting controllers (`IncomeVsExpenseReportController`, `SalesReportController`, `TaskReportController`, `TimelogReportController`, `AttendanceReportController`, `LeadReportController`) into `App\Http\Controllers\Insights`. Placeholder controller exists under `App\Http\Controllers\Core\Insights`.

## Middleware & binding strategy
- Use host middlewares (`auth`, `verified`/`email_verified`, `updateUserActivity`, locale/theme selectors). Replace WorkCore `multi-company-select` with a host-friendly resolver that selects `company_id` and sets a tenant scope.
- Add explicit route model binding for new models via `RouteServiceProvider`.
- Adopt named routes aligned to menu entries (`crm.customers.*`, `work.sites.*`, `money.invoices.*`, `team.attendance.*`, `support.tickets.*`, `insights.reports.*`) to prevent collisions with existing dashboard names.

## AJAX/API alignment
- Separate data endpoints (DataTables/timelog fetchers/report widgets) into `api.php` where appropriate; keep UI routes in core route files.
- Standardise responses using host helpers (JSON resource/response macros) instead of WorkCore inline arrays.
