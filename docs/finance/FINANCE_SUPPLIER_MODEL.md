# FINANCE — SUPPLIER MODEL

The Finance domain reuses `App\Models\Inventory\Supplier` (table: `suppliers`).

**No separate Money\Supplier model is created** — the Inventory domain model is canonical.

Migration 600200 extends the `suppliers` table with:
- `default_account_id` — the default AP account for this supplier

## Fields

| Field              | Notes                                         |
|--------------------|-----------------------------------------------|
| company_id         | tenancy scope (BelongsToCompany trait)        |
| name               | required                                      |
| email              | nullable                                      |
| phone              | nullable                                      |
| tax_number         | nullable                                      |
| payment_terms      | nullable (e.g. "Net 30")                      |
| currency_code      | default USD (inventory); AUD used in AP flows |
| default_account_id | nullable → accounts.id                        |
| status             | active / inactive                             |
| notes              | nullable                                      |

## Finance AP Routes

Suppliers are accessible under `dashboard.money.suppliers.*` via `SupplierController`
in the Money controller namespace.
