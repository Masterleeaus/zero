# Merge readiness checklist

- **Migrations included**: leave tables, leave histories/quotas, expense categories and expenses, business menu seeds, leave/expense menu seed.
- **New routes**: work leaves CRUD, money expenses CRUD, expense categories CRUD; insights extended for expenses/leave conflicts.
- **New domains**: Leave management slice and Expenses slice.
- **Menu entries expected**: operations_leaves, money_expenses, money_expense_categories (MenuSeeder + migration); existing CRM/Work/Money/Support/Notifications/Timelogs/Attendance/Shifts/Agreements/Insights remain.
- **Tests added**: leave conflict coverage, expense metrics and totals, menu presence seed verification.
- **Blockers**: Composer/vendor unavailable in CI environment; run migrations+seed then execute feature tests once vendor is present.
- **First-run validation order**: `php artisan migrate --seed`, verify menu cache refresh (`php artisan optimize:clear` or MenuService regenerate), then hit dashboard for Money > Expenses and Work > Leaves, and run `php artisan test --filter=Feature`.
