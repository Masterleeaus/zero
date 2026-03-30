# Cross-Domain Workflows

## CRM → Work → Money
- **Customer → Site**: sites are scoped by `company_id`; service jobs validate `site_id` against the current company and can optionally bind a customer.
- **Site → Service Job**: jobs inherit site/company context and support statuses `scheduled`, `in_progress`, `completed`, `cancelled`.
- **Service Job → Checklist**: checklist items belong to a job and are created from quote checklist templates during conversion.

## Quote conversions
- **Quote → Service Job**: conversion enforces company/site match, copies customer, title/notes, and seeds checklist items from `checklist_template`.
- **Quote → Invoice**: copies customer, notes, and line items, recomputes subtotal/tax/total server-side, assigns invoice numbers when missing.

## Money lifecycle
- Totals are recomputed from line items; payments recalc `paid_amount`/`balance` and set status to `paid` only when balance clears; void invoices reject payments.
- Signals are now transition-guarded: `QuoteAccepted` fires only on first acceptance, `InvoiceIssued` fires only when moving into `issued`, and `InvoicePaid` fires only when moving into `paid` after balance reaches zero (partial payments stay `partial`).

## Insights
- `/dashboard/insights/overview` surfaces per-company counts for enquiries, customers, sites, jobs, quotes, invoices, overdue invoices, outstanding balances, support tickets by state, and logged hours from timelogs. Support and notification counts stay tenant-scoped via `company_id`.

## Remaining gaps
- Menu entries remain database-driven; seeders now update-or-create keys to avoid duplicates and keep parent/order alignment; verify routes exist during runtime.
- AI/chat surfaces now carry `company_id` (user_openai + chatbot tables) but legacy rows without users may remain null until migrated with full data access.
- Support/notification flows use tenant models but older null-company records may still need cleanup once production data is accessible.
- Timelog slice added (Work → Timelogs) with company scoping; extend attendance/shift imports next.
- Full test execution depends on Composer install; see TESTING notes in PR for current status.
