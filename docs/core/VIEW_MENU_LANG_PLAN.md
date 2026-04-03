# View, Menu, & Language Plan

## View absorption
- Host layouts live under `resources/views/default/layout` with dashboard panels under `resources/views/default/panel`; shared components (`card`, `table`, `navbar`, `bottom-menu`, `floating-menu`, `alerts`, `forms`, etc.) are ready for reuse.
- WorkCore views reside in `resources/views` (dashboard, clients, projects/sites, taskboard/checklists, invoices/payments/expenses, attendance/leaves/shifts, tickets/knowledge-base, calendars, reports) and expect their own sidebar/header wrappers.
- Strategy: lift WorkCore content blocks into native panel layouts and components; strip legacy wrappers and script includes. Reuse host blade components for tables, filters, tabs, modals, and notifications.
- Keep mobile/compact navigation compatible with `bottom-menu` and `floating-menu` components.

## Menu integration
- Navigation is database-driven via `App\Services\Common\MenuService` with `Menu`/`MenuGroup` models and cached menu trees.
- Introduce menu groups aligned to the target domains: **Connect/CRM**, **Work**, **Money**, **Team**, **Insights**, **Support**.
- Add entries only after routes are live; each should reference named routes from `routes/core/*.routes.php` to avoid broken links.
- Preserve existing marketplace/AI menu items; avoid duplicating dashboard root links.

## Language normalisation
- Current host lang files: `lang/en/{auth,pagination,passwords,validation,installer_messages,laraupdater,magicaiupdater}.php` with AI-focused vocabulary.
- WorkCore strings still use client/project/task/employee/estimate/report terminology and lack domain-separated lang files.
- Plan:
  - Add domain files `lang/en/crm.php`, `work.php`, `money.php`, `team.php`, `support.php`, `insights.php`, plus an optional `vertical_overrides.php` for context-sensitive labels.
  - Replace visible strings in absorbed blades with `__('...')` and the normalised vocabulary (customer, enquiry, site, service job, checklist, cleaner, quote, invoice, payment, expense, insight).
  - Align validation messages when moving form requests into host namespaces; avoid duplicating `validation.php`.

## Assets & scripts
- Prefer existing host asset pipeline (Vite + Tailwind configs) rather than importing WorkCore asset mixes.
- Inline scripts from WorkCore blades should be refactored into module-specific JS pushed through existing stacks (`@push('scripts')`) and reuse host utilities where available.
