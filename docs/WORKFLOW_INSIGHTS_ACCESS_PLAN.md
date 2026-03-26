# Workflow, Insights, & Access Plan

## Operational lifecycle
Standardise lifecycle across domains:
1. **Enquiry** captured (lead) → qualification
2. **Quote draft** → **Quote sent** → **Quote approved**
3. **Scheduled service job** (site + time + team) → **Active job**
4. **Checklist / sub-jobs** execution + **Time logs**
5. **Completed job** → **Invoiced** → **Overdue** → **Paid**
6. **Recurring / rebooked** cycles when applicable

State transitions should live in service classes per domain (CRM → Work → Money) with events for notifications and reporting.

## Access & tenancy
- Enforce `company_id` tenant scope everywhere; derive from the authenticated user’s selected company (replacement for WorkCore `multi-company-select` middleware).
- Retain `team_id` for crew assignment (attendance, shifts, job allocation) but do not treat it as tenant isolation.
- Honour host roles/guards (`isAdmin`, `isSuperAdmin`, plan-based feature flags) when exposing WorkCore capabilities.
- Add policy coverage for customers, sites, service jobs, quotes/invoices, payments, attendance/leave, tickets; wire into controllers via `authorizeResource` where feasible.

## Insights & reporting
Prioritised metrics (scoped by `company_id`, optionally filtered by `team_id`):
- Enquiry volume, conversion to approved quotes.
- Quote turnaround (draft→sent→approved timelines).
- Service job volume, completion rates, SLA breaches.
- Timelog utilisation by cleaner/team; attendance vs scheduled shifts.
- Invoiced vs paid amounts; overdue invoice aging; expense trends.
- Revenue by customer and site; recurring revenue retention.
- Support load (tickets/notices) and resolution timing if merged now.

Data sources: WorkCore reporting controllers (`IncomeVsExpenseReportController`, `SalesReportController`, `TaskReportController`, `TimelogReportController`, `LeadReportController`, `AttendanceReportController`) to be rewritten for tenant scope + renamed entities and exposed under `routes/core/insights.routes.php`.

## Surface areas
- Dashboard widgets (existing `DashboardController` / panel widgets) should consume the normalised lifecycle states.
- Dedicated Insights routes/views under `resources/views/default/panel/insights` with cards/tables reused from host components.
- API endpoints for charts should sit in `api.php` or be AJAX endpoints in the modular core routes with proper auth + company scoping.
