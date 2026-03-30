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
- `/dashboard/insights/overview` surfaces per-company counts for enquiries, customers, sites, jobs, quotes, invoices, overdue invoices, and outstanding balances.

## Remaining gaps
- Menu entries remain database-driven; ensure menu seed includes Insights/Work/Money items.
- Full test execution depends on Composer install; see TESTING notes in PR for current status.
