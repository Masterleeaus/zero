# Copilot Task: Complete Insights & Reporting Controller

## Context
This is a Laravel 10 multi-tenant SaaS. The `InsightsController` overview method is already complete with real queries.
The `reports()` method returns a placeholder view — it needs to be a real reporting page.

## File
`app/Http/Controllers/Core/Insights/InsightsController.php`

## Your Task

### 1. Implement the `reports()` method
Replace the placeholder `reports()` stub with a working method that returns a view with these report datasets, all filtered by `$request->user()?->company_id`:

**Revenue Report** (last 12 months, grouped by month):
```php
Invoice::where('company_id', $companyId)
    ->where('status', 'paid')
    ->selectRaw('DATE_FORMAT(updated_at, "%Y-%m") as month, SUM(total) as revenue')
    ->groupBy('month')
    ->orderBy('month')
    ->get()
```

**Jobs by Status** (count per status):
```php
ServiceJob::where('company_id', $companyId)
    ->selectRaw('status, COUNT(*) as total')
    ->groupBy('status')
    ->pluck('total', 'status')
```

**Top Customers by Revenue** (top 10):
```php
Invoice::where('company_id', $companyId)
    ->where('status', 'paid')
    ->join('customers', 'invoices.customer_id', '=', 'customers.id')
    ->selectRaw('customers.name, SUM(invoices.total) as total_paid')
    ->groupBy('customers.id', 'customers.name')
    ->orderByDesc('total_paid')
    ->limit(10)
    ->get()
```

**Expense vs Revenue** (last 6 months side by side):
```php
// Two queries: Expense::totalsByMonth($companyId, 6) and Invoice revenue by month
```

**Leave Summary** (by type, current month):
```php
Leave::where('company_id', $companyId)
    ->whereMonth('start_date', now()->month)
    ->selectRaw('leave_type, COUNT(*) as total')
    ->groupBy('leave_type')
    ->pluck('total', 'leave_type')
```

### 2. Create the view
Create `resources/views/default/panel/user/insights/reports.blade.php`

Use the existing insights overview layout as a template. Include:
- Revenue chart (use Chart.js or Alpine.js + a simple bar/line chart)
- Jobs by status donut chart
- Top customers table
- Leave summary table
- Date range filter (last 30 days / 90 days / 12 months)

### 3. Add route (if missing)
Check `routes/core/insights.routes.php` — ensure `GET /insights/reports` maps to `InsightsController@reports`

## Constraints
- All queries MUST use `->where('company_id', $companyId)` — no cross-tenant data
- Paginate any list with more than 20 rows
- Wrap all queries in try/catch and return empty collections on failure
