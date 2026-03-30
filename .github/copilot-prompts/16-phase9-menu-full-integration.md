# Copilot Task: Phase 9 — Full Menu Integration

## Context
WorkCore merge Phase 9. Navigation is database-driven via `App\Services\Common\MenuService`. Some core menu items exist but the complete menu structure from the cleaning vertical map is not yet implemented.

## Target Menu Structure (from WorkCore `docs/menu-structure.md`)
```
Primary navigation:
1. Customers          → dashboard.crm.customers.index
2. Enquiries          → dashboard.crm.enquiries.index
3. Follow-Ups         → dashboard.crm.enquiries.index (filter=followup) [or dedicated route]
4. Jobs (Sites)       → dashboard.work.sites.index
5. Schedule & Dispatch → dashboard.work.schedule.index [or calendar route]
6. Cleaning Checklist → dashboard.work.service-jobs.index
7. Team               → (parent, no direct route)
   - Cleaners         → dashboard.team.cleaners.index
   - Availability     → dashboard.team.shifts.index
   - Shift Log        → dashboard.team.attendance.index
   - Leave            → dashboard.team.leaves.index
   - Roles            → dashboard.team.roles.index [if exists]
   - Teams            → dashboard.team.teams.index [if exists]
   - Zones            → dashboard.team.zones.index
8. Money              → (parent, no direct route)
   - Quotes           → dashboard.money.quotes.index
   - Invoices         → dashboard.money.invoices.index
   - Payments         → dashboard.money.payments.index
   - Credit Notes     → dashboard.money.credit-notes.index
   - Expenses         → dashboard.money.expenses.index
   - Bank Accounts    → dashboard.money.bank-accounts.index
9. Service Requests   → dashboard.support.index
10. Playbooks         → dashboard.support.knowledgebase.index [future]
11. Insights          → dashboard.insights.overview
```

## Your Task

### 1. Read the current MenuService
Read `app/Services/Common/MenuService.php` in full to understand how menu items are structured.

### 2. Add missing menu entries
For each menu item in the target structure that is NOT already in `MenuService.php`, add it.

Pattern for adding an entry:
```php
'operations_credit_notes' => [
    'label'            => workcore_label('credit_notes', __('Credit Notes')),
    'key'              => 'operations_credit_notes',
    'route'            => 'dashboard.money.credit-notes.index',
    'icon'             => 'receipt-refund', // Heroicon name
    'parent'           => 'operations_money',
    'order'            => 30,
    'active_condition' => ['dashboard.money.credit-notes.*'],
    'show_condition'   => Route::has('dashboard.money.credit-notes.index'),
],
```

### 3. Use workcore_label() for all labels
Replace hardcoded strings with `workcore_label()` calls so the vertical resolver controls display names:
```php
// Instead of:
'label' => __('Sites'),
// Use:
'label' => workcore_label('sites', __('Sites')),
```

### 4. Add Follow-Ups entry
Follow-Ups are enquiries filtered by a flag. Options:
- Add a `follow_up` boolean/date column to `enquiries` table (migration needed)
- Or link to `dashboard.crm.enquiries.index?filter=followup`
- Menu entry should show only when `dashboard.crm.enquiries.index` route exists

### 5. Guard feature-flagged items
For items that aren't built yet (Credit Notes, Bank Accounts, Playbooks, Team Chat):
```php
'show_condition' => workcore_feature('credit_notes') && Route::has('dashboard.money.credit-notes.index'),
```
This hides them until the feature flag is enabled in `config/workcore.php`.

### 6. Create output doc
Create `docs/CORE_MENU_MAP.md` with a table:
| Menu Key | Label | Route | Status (live/feature-flagged/pending) |
