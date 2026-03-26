# Schema, Model, & Tenancy Plan

Tenant doctrine: **company_id = tenant boundary**, **team_id = crew grouping (not isolation)**, **user_id = actor identity**.

## Current state
- Host models: `Company` is user-owned; `Product`, `UserOpenai`, `Chatbot` and others hold `company_id`/`team_id` without a global scope; `Team` manages crew membership. No tenant-aware trait exists today.
- WorkCore models/migrations: most tables include `company_id` + optional `team_id`/`added_by`/`last_updated_by`. No shared scope helper; route group expects `multi-company-select` middleware.

## Tenancy implementation plan
1. Add shared concerns in `app/Models/Concerns`:
   - `BelongsToCompany` (global scope + `company_id` fillable/mutable + `company()` relation).
   - `BelongsToTeam` (helper scope for crew filtering, **not** tenant isolation).
   - Optionally `OwnedByUser` for creator/updater tracking.
2. Apply `BelongsToCompany` to every imported WorkCore model and any host model that becomes tenant-specific (customers, enquiries, sites, service jobs, quotes, invoices, payments, expenses, attendance, leave, shifts, tickets).
3. Ensure validation rules enforce `company_id` presence; derive from authenticated user’s selected company to avoid trusting request payloads.

## Schema consolidation priorities
| Area | WorkCore tables (examples) | Target notes |
| --- | --- | --- |
| CRM / Enquiries / Customers | `customers`, `customer_contacts`, `customer_notes`, `leads`, `lead_contacts`, `lead_custom_forms`, `lead_sources`, `deals`, `proposals`, `gdpr_settings` | Keep as CRM core; rename client → customer, lead → enquiry where meaningful. |
| Sites / Service Jobs / Checklists | `projects` (sites), `project_members`, `project_milestones`, `project_files`, `tasks` (service job/checklist depending on context), `task_labels`, `sub_tasks`, `task_comments`, `timelogs`, `gantt_links`, `discussions` | Convert `projects` to **sites**, map booked work to **service_jobs**, and internal work items to **checklists**. |
| Scheduling / Time | `timelog_breaks`, `weekly_timesheets`, `employee_shift_schedules`, `recurring_events`, `calendars` | Align with service job scheduling and attendance. |
| Quotes / Invoices / Payments / Expenses | `estimates`, `estimate_requests`, `proposals`, `invoices`, `invoice_payment_details`, `credit_notes`, `payments`, `expenses`, `orders`, `bank_accounts`, `recurring_invoices/expenses` | Merge with host finance; reuse gateway/payment processing already in `App\Http\Controllers\Finance`. |
| Workforce | `employees` (cleaners), `departments`, `designations`, `promotions`, `attendances`, `leaves`, `leave_types`, `leaves_quota`, `awards`, `emergency_contacts`, `shift_rotations`, `employee_shift_change_requests` | Maintain `team_id` for crew groupings; tenant scope by `company_id`. |
| Support / Comms | `tickets`, `ticket_replies`, `ticket_files`, `notices`, `knowledge_base`, `discussions` | Integrate with host support surfaces; avoid parallel systems. |
| Reporting | `sales_reports`, `income_vs_expense_reports`, `task_reports`, `timelog_reports`, `attendance_reports` | Rewire queries to tenant scope and renamed entities. |

## Migration strategy
- **Collision check**: compare WorkCore tables against existing host tables before import. If names collide but schemas differ, prefer ALTER migrations over parallel tables.
- **Renaming**: migrate `projects` → `sites`, `tasks` → `service_jobs` or `checklists` based on usage; ensure FK/indices updated.
- **Tenant columns**: add `company_id` (and `team_id` where crew-relevant) to any WorkCore table lacking it; backfill via authenticated company selection.
- **Foreign keys**: align FK references to renamed tables; enforce cascading consistent with host conventions.
- **Timestamps & soft deletes**: retain where present; add soft deletes where WorkCore expects archiving.

## Model reconciliation steps
1. Merge fillables/casts/accessors from WorkCore models into host equivalents or new domain models under the target namespaces.
2. Replace implicit `auth()->user()->id` filters with `company_id` scopes; keep `team_id` filters as optional crew grouping.
3. Update relationships to point to renamed entities (`site` instead of `project`, `serviceJob` instead of `task` where appropriate).
4. Align policy/authorization checks with host guards and roles; avoid custom middleware duplication.
