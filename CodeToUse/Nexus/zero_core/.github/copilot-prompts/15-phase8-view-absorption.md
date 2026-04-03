# Copilot Task: Phase 8 — View Absorption for Missing Domains

## Context
WorkCore merge Phase 8. Basic views exist for the primary controllers (Customer, Enquiry, Site, ServiceJob, Quote, Invoice, Payment, Expense, Attendance, Leave, Shift, Timelog, ServiceAgreement). Views are needed for all new controllers from Phase 7 and for enriching existing index/show pages with sub-panels.

## Rules (from WORKCORE_MERGE.md)
- Use MagicAI native layouts: `@extends('default.layout.app')` or equivalent panel layout
- Use host blade components: `<x-card>`, `<x-table>`, `<x-form>`, `<x-badge>`, `<x-modal>`
- NO standalone wrappers, no WorkCore sidebar/header
- Inline scripts go in `@push('scripts')` using existing Vite/Alpine stacks
- All strings through `__()` helper using domain lang keys

## Your Task

### 1. Study the existing pattern
Read these existing views first to understand the layout pattern:
- `resources/views/default/panel/user/crm/customers/index.blade.php`
- `resources/views/default/panel/user/money/quotes/form.blade.php`
- `resources/views/default/panel/user/work/service-jobs/show.blade.php`

### 2. Add sub-panels to existing show pages

#### Customer Show Page enrichment
In `resources/views/default/panel/user/crm/customers/show.blade.php`, add tabbed sections:
- **Contacts tab**: table of CustomerContact records + inline add form
- **Notes tab**: pinnable notes list + add note form
- **Documents tab**: file upload + document list with download links
- **Deals tab**: list of linked deals with status badges
- **Quotes tab**: list of customer's quotes
- **Invoices tab**: list of customer's invoices with balance

Use Alpine.js `x-data="{ tab: 'contacts' }"` for tab switching.

#### Deal Board View
Create `resources/views/default/panel/user/crm/deals/board.blade.php`:
- Kanban columns: Open | Won | Lost
- Each card shows: deal title, value, customer name, close date
- Drag-and-drop via Alpine.js Sortable plugin (already in host)
- POST on drop to `dashboard.crm.deals.update` with new status

### 3. New index + form views for each new controller

For each of the following, create `index.blade.php` and `form.blade.php`:

**CRM:**
- `crm/deals/` — index with board/list toggle, form with customer picker + value/status/close date
- `crm/customers/contacts/` — small table, inline modal form (embedded in customer show)

**Money:**
- `money/credit-notes/` — index with status badge filter, form with customer + invoice picker + line items
- `money/quote-templates/` — index list, form with name + line items (same line-item component as quotes)
- `money/bank-accounts/` — simple index + form (bank name, account details, default toggle)
- `money/taxes/` — simple index + form (name, rate %, default toggle)

**Team:**
- `team/zones/` — index with color swatches, simple form
- `team/cleaner-profiles/` — index list, detailed form (personal info, hire date, ABN, emergency contact)
- `team/timesheets/` — index with status filter, show page with week summary, approve/reject buttons

### 4. Reuse the line-item editor component
The quote/invoice line-item editor is already built. Extract it as a reusable Blade component:
- Create `resources/views/components/line-item-editor.blade.php`
- Accept `$items` and `$currency` props
- Use in: QuoteForm, InvoiceForm, CreditNoteForm, QuoteTemplateForm

### 5. Create output doc
Create `docs/VIEW_ABSORPTION_PLAN.md` listing each view file, which layout it extends, and completion status.
