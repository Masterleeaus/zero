# Schema, Model, & Tenancy Plan

Tenant doctrine: **company_id = tenant boundary**, **team_id = crew grouping (not isolation)**, **user_id = actor identity**.

## Current state
- Host models: `Company` is user-owned; `Product` and CRM models (`Customer`, `Enquiry`) use `BelongsToCompany` for tenant scoping; Work models (`Site`, `ServiceJob`, `Checklist`, `Timelog`) also use `BelongsToCompany`; Money models (`Quote`, `Invoice`, `Payment`) use the same traits. **AI surfaces now carry `company_id`** on `user_openai`, `user_openai_chat`, `user_openai_chat_messages`, and chatbot tables (`chatbot`, `chatbot_data`, `chatbot_data_vectors`, `chatbot_history`) with `BelongsToCompany` applied; backfill sets company from users where available. Support/notifications were updated to carry `company_id` on `user_support`, `user_support_messages`, `notifications`, and `usage` with `BelongsToCompany` applied. `Team` manages crew membership. Tenant-aware traits live in `app/Models/Concerns`.
- WorkCore models/migrations: most tables include `company_id` + optional `team_id`/`added_by`/`last_updated_by`. No shared scope helper; route group expects `multi-company-select` middleware. Attendance/shifts/agreements migrations now default to tenant-safe status values and include support resolved timestamps.

## Tenancy implementation plan
1. Shared concerns in `app/Models/Concerns`:
   - `BelongsToCompany`: conditional global scope keyed to the authenticated user’s `company_id`, auto-fill on create, and a typed `scopeForCompany`.
   - `BelongsToTeam`: `team()` relation and `scopeForTeam` for crew grouping (not tenant isolation).
   - `OwnedByUser`: auto-fill `created_by` from the authenticated user with `scopeCreatedBy` + `creator()` relation.
2. Apply `BelongsToCompany` to every imported WorkCore model and any host model that becomes tenant-specific (customers, enquiries, sites, service jobs, quotes, invoices, payments, expenses, attendance, leave, shifts, tickets). Host `Product`, CRM `Customer`/`Enquiry`, Work `Site`/`ServiceJob`/`Checklist`, and Money `Quote`/`Invoice`/`Payment` are now scoped this way; `ServiceJob` now also holds `customer_id` for quote conversion.
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
- **Finance tables**: host lacked quote/invoice tables, so `quotes` and `invoices` were introduced with `company_id` tenancy and links to customers/quotes. Lifecycle fields added (quote_number/invoice_number, subtotal, tax, totals, paid_amount/balance, valid_until, site_id, checklist_template). A new `payments` table was created for invoice payments (company-scoped). Existing gateway/subscription payment tables remain separate and were not reused. Line-item tables `quote_items` and `invoice_items` were added with company_id + created_by for reconciliation; totals are recomputed from items.
- **Line items**: canonical `quote_items` and `invoice_items` tables carry `description`, `quantity`, `unit_price`, `tax_rate`, `line_total`, and `sort_order`; totals on quotes/invoices are recomputed from items to keep subtotal/tax/total aligned.
- **Tenant columns**: add `company_id` (and `team_id` where crew-relevant) to any WorkCore table lacking it; backfill via authenticated company selection. `service_jobs` gained `customer_id` for quote conversion plus `quote_id` and optional `assigned_user_id` for workforce prep; `checklists` carry optional `assigned_user_id`.
- **Migration safety**: lifecycle migration avoids brittle column renames by additive `quote_number`/`invoice_number` fields with backfill from legacy `number` columns when present; DBAL not required for column rename. Paid/balance and subtotal/tax fields are additive. Unique indexes on `(company_id, quote_number)` and `(company_id, invoice_number)` enforce numbering; rollbacks drop the named indexes. Service job/customer/site links remain company-scoped; additive migration `2026_03_30_194500_add_assignment_and_quote_links_to_work_tables` adds quote/assignee columns with safe drops on rollback.
- **Foreign keys**: align FK references to renamed tables; enforce cascading consistent with host conventions. Attendance defaults to `planned`; shifts include optional service job linkage; support tickets include `resolved_at` with null-safe rollbacks.
- **Timestamps & soft deletes**: retain where present; add soft deletes where WorkCore expects archiving.

## Model reconciliation steps
1. Merge fillables/casts/accessors from WorkCore models into host equivalents or new domain models under the target namespaces.
2. Replace implicit `auth()->user()->id` filters with `company_id` scopes; keep `team_id` filters as optional crew grouping.
3. Update relationships to point to renamed entities (`site` instead of `project`, `serviceJob` instead of `task` where appropriate).
4. Align policy/authorization checks with host guards and roles; avoid custom middleware duplication.
