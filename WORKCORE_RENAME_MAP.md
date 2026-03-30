## WorkCore → Titan Vocabulary Rename Map

| Old name | New name | Type | Reason | Compatibility / follow-up |
| --- | --- | --- | --- | --- |
| clients | customers | domain language (UI/routes/models) | Align CRM wording with Titan service vocabulary | All CRM controllers/models already use `Customer`; verify any imported WorkCore views or translations still using “client” before enabling |
| client_details | customer_details | table | Customer profile metadata | Table not present in host migrations; if importing legacy data, create forward migration that writes into `customer_details` |
| client_contacts | customer_contacts | table | Contact records belong to customers | Not present in host migrations; map incoming data to `customer_contacts` when ingesting WorkCore exports |
| projects | service_jobs | tables/models/routes/views | Service delivery jobs replace generic projects | Host migrations already create `service_jobs`; no `projects` tables exist. If ingesting legacy tables, rename to `service_jobs` with FK/index updates |
| project_departments | service_job_departments (preferred) | table | Department tag per job | Table absent; if encountered during import, create `service_job_departments` and remap relations |
| project_label_list | service_job_label_list | table | Label dictionary for jobs | Absent in host schema; ensure any legacy seeds are written to the new table name |
| project_labels | service_job_labels | table/pivot | Job-to-label assignment | Absent; pivot should reference `service_jobs` and `service_job_label_list` |
| project_milestones | service_job_milestones | table | Track milestones per job | Absent; create/rename accordingly on import |
| project_status_settings | service_job_status_settings | table/config | Status catalogue for jobs | Absent; map in any incoming config |
| project_sub_categories | service_job_sub_categories | table | Sub-categories for jobs | Absent; map on import |
| project_template_milestone | service_job_template_milestones | table | Template milestones for job templates | Absent; rename if template data is restored |
| project_template_tasks | checklist_template_items | table | Template items for checklists | Absent; map during migration |
| project_templates | service_job_templates | table/models | Templates for jobs | Absent; map on import |
| project_time_logs | timelogs (service_job_id FK) | table | Job time tracking | Host uses `timelogs` scoped to jobs; migrate legacy `project_time_logs` into `timelogs` and update FK/index names to reference `service_job_id` |
| tasks | checklists | tables/models/routes/views | Checklists replace generic tasks | Host migrations create `checklists`; no `tasks` table exists |
| task_comments | checklist_comments | table | Comments on checklist items | Absent; map if legacy comments imported |
| task_comment_emoji | checklist_comment_emoji | table | Emoji reactions on comments | Absent; map on import |
| task_notes | checklist_notes | table | Notes for checklist items | Absent; map on import |
| task_settings | checklist_settings | table/config | Checklist options | Absent; map on import |
| tickets | service_issues | domain language/tables/routes | Rename support tickets to service issues | Current host uses `user_support` with `ticket_id`; renaming still pending (see schema notes) |
| ticket_activities | service_issue_activities | table | Activity log for service issues | Not present; map legacy data when support domain is renamed |
| ticket_custom_forms | service_issue_custom_forms | table | Custom fields | Not present; map on support rename |
| ticket_replies | service_issue_replies | table | Conversation replies | Not present; map on support rename |
| ticket_settings_for_agents | service_issue_settings_for_agents | table/config | Agent preferences | Not present; map on support rename |
| “service jobs” (spaced) | service_jobs | table name normalisation | Remove space in table identifiers | No occurrences detected in host schema; keep guardrails during imports |
| “service agreements” (spaced) | service_agreements | table name normalisation | Remove space in table identifiers | Host uses `service_agreements`; ensure imports strip spaces |

### Tenant/identity doctrine
- `company_id` = tenant boundary
- `team_id` = internal crew grouping (do **not** use as tenant boundary)
- `user_id` = actor identity

### Shared/core platform tables (do not rename)
`company_addresses`, `custom_field_group`, `custom_fields`, `custom_link_settings`, `device_user`, `flags`, `front_details`, `global_currencies`, `global_invoice_settings`, `global_invoices`, `global_payment_gateway_credentials`, `global_settings`, `global_subscriptions`, `language_settings`, `message_settings`, `modules`, `notification_settings`, `offline_payment_methods`, `offline_plan_changes`, `packages`, `package_update_notifies`, `payment_gateway_credentials`, `pinned`, `push_notification_settings`, `pusher_settings`, `qrcode`, `roles`, `theme_settings`, `user_permissions`.
