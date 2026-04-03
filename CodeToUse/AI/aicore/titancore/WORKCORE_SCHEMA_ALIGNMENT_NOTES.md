## WorkCore Schema Alignment Notes

### Current host state
- Active tables use Titan vocabulary: `customers`, `enquiries`, `sites`, `service_jobs`, `checklists`, `timelogs`, `shifts`, `attendances`, `service_agreements`, quotes/invoices/payments, expenses, leave/leave_quotas/leave_histories.
- Core Work models already point to the aligned tables (`ServiceJob`, `Checklist`, `Site`) with `company_id` + `team_id` preserved.
- No legacy `projects`/`tasks`/`clients` tables are present in host migrations.
- Support domain still uses `user_support` with `ticket_id` route keys and ticket terminology.

### Required forward migrations when importing legacy WorkCore data
1. **Rename legacy tables to aligned names**: apply renames in forward-only migrations (e.g., `projects` → `service_jobs`, `tasks` → `checklists`, `project_time_logs` → `timelogs` with `service_job_id` FK). Update indexes/FK names accordingly.
2. **Support domain rename**: convert `user_support` + `ticket_*` columns to `service_issues` naming (`issue_id`, `service_issue_messages`, etc.) and update policies/routes/lang. Pending — not executed in host code.
3. **Department/label/template tables**: if WorkCore exports include `project_departments`, `project_labels`, `project_templates`, create corresponding `service_job_*` tables before import.
4. **Checklist templates**: map `project_template_tasks` to `checklist_template_items` if template data is restored.

### Identity/tenancy
- All new work/CRM/money tables include `company_id`; many also include `team_id` and `created_by/assigned_user_id`.
- Tenancy traits (`BelongsToCompany`, `OwnedByUser`) are already applied to Work/CRM models; keep `team_id` for crew grouping only.

### Outstanding gaps / manual follow-up
- **Support tickets → service issues** rename not yet implemented; impacts models (`UserSupport*`), routes, controllers, notifications, lang keys, factories, and tests.
- Verify any imported views/lang from WorkCore ZIPs do not reintroduce `client/project/task` wording before enabling.
- If legacy “service jobs” / “service agreements” (with spaces) appear in SQL exports, normalize to underscored names during import.

### Testing considerations
- Full test/CI execution is currently blocked in this environment because dependency install requires private packages (e.g., `yoomoney/yookassa-sdk-php`). Execute migrations and tests after dependencies are available in a networked environment.
