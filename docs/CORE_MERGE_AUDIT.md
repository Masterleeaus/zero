# Core Merge Audit

Scan completed against the MagicAI host repository (`/home/runner/work/zero/zero`) and the prepared WorkCore source (`WorkCore.zip` extracted to `/tmp/workcore`). This log captures what exists today, where conflicts sit, and what must be normalised before any code moves.

## Host snapshot (MagicAI)
- **Routes**: `routes/panel.php` is the main dashboard monolith; `routes/api.php`, `routes/web.php`, `routes/auth.php`, plus `routes/webhooks.php`, `routes/channels.php`, `routes/console.php`.
- **Controllers**: AI/marketplace/chatbot first-party controllers (e.g. `Dashboard/*`, `Finance/GatewayController`, `Finance/PaymentProcessController`, `Team/TeamController`, `Chatbot/*`).
- **Providers**: `AppServiceProvider`, `RouteServiceProvider`, `ViewServiceProvider`, `AuthServiceProvider`, `BroadcastServiceProvider`, `EventServiceProvider`, `ChatbotServiceProvider`, `ExtensionServiceProvider`, `MacrosServiceProvider`, `AwsServiceProvider`, `TelescopeServiceProvider`.
- **Models/tenancy**: `Company` (user-owned, no global scope), `Product`/`UserOpenai`/`Chatbot` use `company_id`/`team_id` inconsistently, `Team` + `TeamMember` for crew grouping. No tenant global scopes are registered.
- **Navigation**: dynamic menu via `App\Services\Common\MenuService` + `App\Models\Common\Menu`/`MenuGroup`; rendered with existing panel components (`resources/views/default/components/navbar`, `floating-menu`, `bottom-menu`, `table`, `card`, etc.).
- **Views**: native layouts under `resources/views/default/layout` and `resources/views/default/panel/*`.

## WorkCore snapshot (pre-merge package)
- **Routes**: a single monolithic `routes/web.php` (~73KB) under middleware `['auth', 'multi-company-select', 'email_verified']` with `account` prefix. No modular split.
- **Controllers**: CRM (Client/Lead/Deal/Proposal/Contact/Category/Gdpr), Work (Project/Site, Task/SubTask, Timelog, Calendar/Gantt), Finance (Invoice/Estimate/Quote/CreditNote/Expense/Payment/Order/BankAccount), Workforce (Employee/Cleaner, Attendance, Leave, Shift, Promotion, Department/Designation), Support (Ticket/Notice/KnowledgeBase/Discussion), Settings (Currency/Tax/Module/Notification/Smtp/GoogleCalendar/etc.).
- **Models**: numerous under `app/Models` carrying `company_id` + optional `team_id`/`user_id`, but no shared tenant scope helper.
- **Migrations**: hundreds in `database/migrations` covering customers/enquiries, sites/service jobs/checklists/scheduling, quotes/invoices/payments/expenses, workforce/attendance/leave/shifts, and reporting.
- **Views**: `resources/views` contains dashboards, clients, projects/sites, taskboard/checklists, invoices/payments/expenses, attendance/leaves/shifts, tickets/knowledge-base, calendars, and reporting blades.
- **Config/boot**: `config/workcore.php`, `config/vertical_language.php`; stub provider `app/Providers/SuperAdmin/EventServiceProvider.php`; no standalone kernel/auth kept.
- **Language**: vertical labels + scattered domain strings; still uses legacy vocabulary (client/project/task/employee/estimate/report).

## Collision & risk highlights
- **Name overlap**: `DashboardController`, `SettingsController`, `NotificationController`, `SearchController`, `Payment*`, `Invoice*`, `OrderController`, `Gdpr*`, `Attendance*`, `Leave*` exist in both worlds; must merge logic, not duplicate routes.
- **Tenancy gap**: host has no `company_id` global scope; WorkCore expects multi-company selection and `company_id` columns. `team_id` must remain crew-only, not tenant isolation.
- **Vocabulary drift**: WorkCore uses client/project/task/employee/estimate/report; target terms are customer/site/service job/checklist/cleaner/quote/insights.
- **Boot assumptions**: WorkCore route group relies on `multi-company-select` + `email_verified` middleware that are not part of the host; integration must map to existing auth/verification stack.
- **Navigation/UI**: WorkCore blades assume their own sidebar/header; they must be re-skinned to native `resources/views/default/panel` layouts and wired to the dynamic menu system (no duplicate menu helpers).

## Immediate audit follow-ups
- Enumerate table/column collisions before migrating (customers/enquiries/sites/service_jobs/checklists/finance/workforce/support).
- Slice WorkCore route monolith into domain route files before wiring into `RouteServiceProvider`.
- Reconcile payments/gateway overlap with existing `Finance/*` controllers/services.
- Map WorkCore policies/middleware to host auth/permission model and cache strategy.
- Normalise language files before exposing UI (avoid mixed client/project/task labels).
