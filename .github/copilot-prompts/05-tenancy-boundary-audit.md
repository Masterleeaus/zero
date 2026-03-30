# Copilot Task: Audit & Fix Tenancy Boundary (company_id vs team_id)

## Context
Laravel 10 multi-tenant SaaS. The primary tenant boundary is `company_id`.
After the WorkCore merge, some legacy code paths still use `team_id` for data isolation,
creating a risk of cross-team data leakage within the same company.

## Your Task

### Step 1: Find all team_id usage in controllers and services
Run these searches:
```bash
grep -rn "team_id" app/Http/Controllers/ --include="*.php"
grep -rn "team_id" app/Services/ --include="*.php"
grep -rn "->where('team_id'" app/ --include="*.php"
grep -rn "team_id.*auth\|auth.*team_id" app/ --include="*.php"
```

### Step 2: For each occurrence, determine:
- Is this scoping data access? → Should use `company_id` instead
- Is this assigning a resource to a team? → Keep as is (team assignment is valid)
- Is this filtering a query for display? → Should use `company_id` as primary filter, `team_id` as secondary

### Step 3: Fix the data-access scoping
Pattern to convert:
```php
// OLD (wrong — leaks across teams in same company)
->where('team_id', auth()->user()->team_id)

// NEW (correct — scoped to company, team is optional secondary filter)
->where('company_id', auth()->user()->company_id)
->when($teamId, fn($q) => $q->where('team_id', $teamId))
```

### Step 4: Check `app/Services/Stream/StreamService.php`
This file has mixed company_id/team_id checks. Review and ensure:
- Primary isolation = `company_id`
- `team_id` is only used for sub-filtering within a company

### Step 5: Check all new Core controllers
Review these files for consistent `company_id` usage:
- `app/Http/Controllers/Core/Crm/CustomerController.php`
- `app/Http/Controllers/Core/Crm/EnquiryController.php`
- `app/Http/Controllers/Core/Work/ServiceJobController.php`
- `app/Http/Controllers/Core/Work/SiteController.php`
- `app/Http/Controllers/Core/Money/QuoteController.php`
- `app/Http/Controllers/Core/Money/InvoiceController.php`

These should rely on the `BelongsToCompany` global scope — not manually add `->where('company_id')`.
If they are double-scoping (global scope + manual where), remove the redundant manual where.

### Step 6: Document findings
Create `docs/tenancy-audit-results.md` listing:
- Files changed
- Pattern of change (team_id → company_id)
- Any edge cases kept as team_id

## Constraints
- Do NOT change team assignment logic (e.g. `$job->team_id = $teamId` assignments are fine)
- Only change DATA FILTERING queries
- Run `php artisan test` after each file change to catch regressions
