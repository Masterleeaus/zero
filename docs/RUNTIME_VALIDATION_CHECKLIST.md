# Runtime Validation Checklist

Use after dependencies install and migrations are runnable.

## Migrations (order)
1. `php artisan migrate` (includes AI/chat company columns, work/money tables, support/notification/usage company backfill)
2. Re-run seeders that update menus (if available) or manual `MenuService::warm()` via request.

## Seeds
- Ensure menu seed runs idempotently for keys: `crm`, `work`, `money`, `insights` under the user panel group.

## Tests (targeted)
- Route smoke: ensure named routes exist for CRM, Work, Money, Insights, Support (`dashboard.support.*`), Notifications (`dashboard.notifications.*` if defined).
- Model factory coverage: `Quote`, `Invoice`, `ServiceJob`, `Checklist`, `Customer`, `Enquiry` (add missing ones before broader tests).
- Timelog/Support smoke: `dashboard.work.timelogs.*` list/create/stop; support ticket view/send guarded by policy; notifications list/read scoped by company.

## Manual validation order
1. Log in as a company-scoped user; confirm menu entries resolve without 404s.
2. Create a support ticket and reply as admin/user; verify `company_id` set on `user_support` and `user_support_messages`.
3. Trigger a notification (e.g., ticket reply); verify `notifications.company_id` matches the user and scoped reads return only same-company rows.
4. Create quote → invoice and quote → service job conversions; verify totals and company scoping.
5. Start/stop timelog; confirm durations computed and filtered by company.
6. View Insights dashboard; confirm counts respect company boundaries and match created records including support/timelog summaries.

## Expected blockers
- Composer install currently fails (ext-redis version + blocked `git.yoomoney.ru` host); all runtime checks wait on dependency install.
- Migrations/tests cannot run until vendors are installed and database connectivity is available.
