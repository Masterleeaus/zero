# Copilot Task: Fix N+1 Query Problems in Core Controllers

## Context
Laravel 10 SaaS. The new Core controllers (CRM, Work, Money) load index pages without eager loading related models, causing N+1 queries on large datasets.

## Your Task

### Step 1: Add eager loading to all index() methods

#### `app/Http/Controllers/Core/Money/QuoteController.php` — `index()`
```php
// Add to the query:
->with(['customer', 'items'])
```

#### `app/Http/Controllers/Core/Money/InvoiceController.php` — `index()`
```php
->with(['customer', 'items', 'payments'])
```

#### `app/Http/Controllers/Core/Money/PaymentController.php` — `index()`
```php
->with(['invoice', 'invoice.customer'])
```

#### `app/Http/Controllers/Core/Money/ExpenseController.php` — `index()`
```php
->with(['category'])
```

#### `app/Http/Controllers/Core/Work/ServiceJobController.php` — `index()`
```php
->with(['site', 'assignedUser', 'quote', 'agreement'])
```

#### `app/Http/Controllers/Core/Work/SiteController.php` — `index()`
```php
->with(['customer', 'jobs'])
```

#### `app/Http/Controllers/Core/Crm/CustomerController.php` — `index()`
```php
->with(['enquiries'])
```

#### `app/Http/Controllers/Core/Crm/EnquiryController.php` — `index()`
```php
->with(['customer', 'assignedUser'])
```

### Step 2: Add pagination defaults
All index methods should paginate results. If any return `->get()` on a potentially large dataset, change to:
```php
->paginate(25)
```
And pass `$results->withQueryString()` to the view.

### Step 3: Add missing database indexes via migration
Create a new migration: `php artisan make:migration add_performance_indexes_to_core_tables`

Add indexes:
```php
// service_jobs
Schema::table('service_jobs', function (Blueprint $table) {
    $table->index(['company_id', 'status']);
    $table->index(['company_id', 'assigned_user_id']);
    $table->index('service_agreement_id');
    $table->index('scheduled_at');
});

// quotes
Schema::table('quotes', function (Blueprint $table) {
    $table->index(['company_id', 'status']);
    $table->index(['company_id', 'customer_id']);
});

// invoices
Schema::table('invoices', function (Blueprint $table) {
    $table->index(['company_id', 'status']);
    $table->index(['company_id', 'due_date']);
    $table->index('quote_id');
});

// attendance
Schema::table('attendance', function (Blueprint $table) {
    $table->index(['company_id', 'user_id']);
    $table->index(['company_id', 'status']);
});
```

### Step 4: Fix the InsightsController overview
The overview makes 20+ individual queries. Wrap related queries in a single DB call or cache the results for 5 minutes:
```php
$stats = Cache::remember("insights_overview_{$companyId}", 300, function () use ($companyId) {
    // all the queries
    return compact('enquiries', 'customers', ...);
});
```

## Constraints
- Do NOT change query results — only add `with()` and `paginate()`
- Use `withQueryString()` on paginator for filter persistence
- Run `php artisan test` after changes
