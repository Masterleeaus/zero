# Core Domain Target Map

Targets map WorkCore capability into native MagicAI core areas. Routes must be modular, controllers should extend/merge existing host patterns, and views must reuse `resources/views/default/panel` layouts.

## Domain-to-core mapping
| Domain | WorkCore scope | Target route file | Target controllers/models | Target views | Menu grouping |
| --- | --- | --- | --- | --- | --- |
| CRM / Enquiries / Customers | Clients, Contacts, Leads, Deals, Proposals, GDPR consent | `routes/core/crm.routes.php` | `App\Http\Controllers\Crm\*`, `App\Models\Crm\*` (customers, enquiries, pipelines) | `resources/views/default/panel/crm/*` | **Connect** |
| Work (Sites / Service Jobs / Checklists / Scheduling) | Projects/Sites, Tasks/SubTasks, Checklists, Timelogs, Calendars, Gantt, Discussions | `routes/core/work.routes.php` | `App\Http\Controllers\Work\*`, `App\Models\Work\*` (sites, service_jobs, checklists, timelogs, discussions) | `resources/views/default/panel/work/*` | **Work** |
| Money (Quotes / Invoices / Payments / Expenses) | Estimates/Quotes, Invoices, Credit Notes, Payments, Expenses, Orders, Bank Accounts | `routes/core/money.routes.php` | `App\Http\Controllers\Money\*` (bridge to existing `Finance\GatewayController`/`PaymentProcessController`), `App\Models\Money\*` | `resources/views/default/panel/money/*` | **Money** |
| Team / Workforce | Employees/Cleaners, Attendance, Leaves, Shifts, Promotions, Departments/Designations, Awards | `routes/core/team.routes.php` | `App\Http\Controllers\Teamwork\*`, `App\Models\Teamwork\*` (attendance/leave/shift) | `resources/views/default/panel/team/*` | **Team** |
| Support / Comms | Tickets, Notices, Knowledge Base, Discussions, Public URLs | `routes/core/support.routes.php` | `App\Http\Controllers\Support\*`, `App\Models\Support\*` (reuse existing `Dashboard\SupportController` if possible) | `resources/views/default/panel/support/*` | **Support** |
| Insights / Reporting | Sales/Income vs Expense, Task/Timelog reports, Lead/Conversion dashboards, Attendance/Leave summaries | `routes/core/insights.routes.php` | `App\Http\Controllers\Insights\*`, report queries scoped by `company_id` | `resources/views/default/panel/insights/*` | **Insights** |

## Cross-cutting infrastructure
- Shared concerns: `company_id` tenant scope + optional `team_id` crew grouping; add reusable traits in `App\Models\Concerns` for all imported models.
- Requests/validation: place under `App\Http\Requests/{Domain}` and reuse host form/response helpers.
- Events/observers: co-locate under `app/Events/{Domain}` and `app/Observers/{Domain}`; hook via service providers.
- Console/automation: shift scheduling/leave quotas/timelog approvals → `app/Console/Commands/{Domain}` with `Kernel` registration.

## Placement rules
1. Reuse or extend existing host controllers/services before creating new ones (e.g., payments should leverage `Finance\GatewayController`/`PaymentProcessController`).
2. Prefer merging WorkCore view content into existing `resources/views/default/panel` layout + components (`card`, `table`, `navbar`, `bottom-menu`) instead of shipping standalone layouts.
3. All domain routes load through a core loader in `RouteServiceProvider` to avoid reintroducing `routes/web.php` monoliths.
