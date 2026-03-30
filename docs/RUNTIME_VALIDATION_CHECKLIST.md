# Runtime Validation Checklist

Use after dependencies install and migrations are runnable.

## First-run execution order
1. `composer install` (or `composer run install:no-redis` where supported).
2. `php artisan migrate`.
3. `php artisan db:seed --class=MenuSeeder` (idempotent, surfaces attendance/shifts/agreements/timelogs/support/notifications/insights).
4. Targeted tests: `phpunit --filter AgreementSchedulerTest`, `phpunit --filter ShiftAssignmentTest`, `phpunit --filter AttendanceStateMachineTest`, `phpunit --filter SupportLifecycleTest` (full suite after vendors succeed).
5. Manual panel validation (see below).

## Seeds
- Menu seed is idempotent for CRM, Work (sites, service jobs, attendance, shifts, timelogs, agreements), Money (quotes/invoices), Support, Notifications, and Insights.

## Tests (targeted)
- Route smoke: `dashboard.work.*` (attendance/shifts/timelogs/agreements), `dashboard.support.*`, `dashboard.user.notifications.*`, `dashboard.insights.*`.
- Lifecycle: agreement scheduler structured results, shift assignment endpoint, attendance missed state, support lifecycle transitions.
- Notifications: unread/mark-all-read remain company-scoped; cache invalidation after mark-as-read.

## Manual validation order
1. Log in as a company-scoped user; confirm menu entries resolve without 404s.
2. Create a support ticket and reply as admin/user; verify status flips (`waiting_on_user`/`waiting_on_team`) and `resolved_at` timestamps on resolve; ensure stale/auto-resolve hooks run.
3. Trigger a notification (e.g., ticket reply); verify `notifications.company_id` matches the user and scoped reads return only same-company rows.
4. Create quote → invoice and quote → service job conversions; verify totals and company scoping.
5. Start/stop timelog; confirm durations computed and filtered by company.
6. Check attendance: check-in sets `checked_in`, checkout records duration, late/missed helpers work; Insights reflects attendance summary and due agreements.
7. View Insights dashboard; confirm counts respect company boundaries and match created records including support/timelog summaries.

## Expected blockers
- Composer install currently fails (ext-redis version + blocked `git.yoomoney.ru` host); all runtime checks wait on dependency install.
- Migrations/tests cannot run until vendors are installed and database connectivity is available.
