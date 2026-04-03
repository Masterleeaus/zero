# Copilot Task: Add Test Coverage for Core WorkCore Models

## Context
Laravel 10 with Pest PHP testing. The WorkCore merge added Money, Work, and CRM models with zero test coverage. Tests use SQLite in-memory (see `phpunit.xml`).

## Framework
- **Pest PHP** v2 with Laravel plugin
- Tests live in `tests/Feature/` and `tests/Unit/`
- See existing tests in `tests/Feature/` for patterns

## Your Task
Create the following test files. Each should use `RefreshDatabase` trait.

---

### 1. `tests/Feature/Models/Money/QuoteTest.php`
Test the `Quote` model:
- A quote belongs to a company (`BelongsToCompany` global scope filters correctly)
- `quote_number` is unique per company
- `status` transitions: draft → sent → approved → converted
- `Quote::accepted()` scope returns only `approved` and `accepted` statuses
- Relationship: `quote->items()` returns `QuoteItem` records
- Relationship: `quote->customer()` returns a `Customer`
- `total` is computed correctly from items (if auto-computed)

### 2. `tests/Feature/Models/Money/InvoiceTest.php`
Test the `Invoice` model:
- `invoice_number` is unique per company
- `recomputeBalance()` sets `balance = total - paid_amount`
- Status transitions: draft → issued → paid, draft → overdue, issued → void
- Relationship: `invoice->items()` returns `InvoiceItem` records
- Relationship: `invoice->payments()` returns `Payment` records
- `InvoiceIssued` event fired when status changes to `issued`
- `InvoicePaid` event fired when status changes to `paid`

### 3. `tests/Feature/Models/Work/ServiceJobTest.php`
Test the `ServiceJob` model:
- Job belongs to a company (global scope)
- Status transitions: scheduled → active → completed / cancelled
- Relationship: `job->site()`, `job->quote()`, `job->agreement()`
- Unassigned scope: `ServiceJob::whereNull('assigned_user_id')`

### 4. `tests/Feature/Models/Crm/CustomerTest.php`
Test the `Customer` model:
- Customer belongs to a company
- Relationship: `customer->enquiries()`, `customer->quotes()`, `customer->invoices()`
- Soft deletes: deleted customer does not appear in default queries

### 5. `tests/Feature/Tenancy/TenancyBoundaryTest.php`
Critical tenant isolation tests:
- User from company A cannot see company B's quotes/invoices/jobs/customers
- `BelongsToCompany` global scope applies automatically when user is authenticated
- `withoutGlobalScope('company')` bypasses the filter for admin use

## Pattern to Follow
```php
// Example pattern from existing tests
it('scopes quotes to the authenticated user company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $userA = User::factory()->for($companyA)->create();

    Quote::factory()->for($companyA)->count(3)->create();
    Quote::factory()->for($companyB)->count(2)->create();

    actingAs($userA);

    expect(Quote::count())->toBe(3);
});
```

## Constraints
- Use `actingAs($user)` to set auth context for global scope tests
- Use factories — create them if they don't exist (`database/factories/`)
- Each `it()` block should be independent
- No real HTTP requests — test models and services directly
