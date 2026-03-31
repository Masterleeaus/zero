# Phase 8 View Absorption Plan

- Introduce native dashboard views for CRM deals, Money credit notes, quote templates, bank accounts, taxes, and Team zones/cleaner profiles/timesheets using the default layout.
- Enrich Customer detail with WorkCore-style tabs for contacts, notes, documents, deals, quotes, and invoices.
- Extract a reusable line-item editor Blade component and reuse it across quote, invoice, credit note, and quote template forms.
- Populate demo data via `App\Support\WorkcoreDemoData` to mirror WorkCore feeds until live data is wired.
- Keep inline scripts inside Blade stacks and prefer native components (`x-table`, `x-card`, `x-button`) for consistency.
