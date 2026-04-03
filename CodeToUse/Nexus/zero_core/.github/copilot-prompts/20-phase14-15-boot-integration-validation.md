# Copilot Task: Phase 14 & 15 — Boot Integration + Full Validation

## Context
WorkCore merge Phases 14 & 15. The final integration and validation pass to confirm the full merged system boots cleanly, routes load, migrations run, and core workflows pass.

---

## Phase 14 — Boot Process Integration

### 1. Register WorkCoreServiceProvider
Ensure `App\Providers\WorkCoreServiceProvider::class` is in `config/app.php` providers array.
(See prompt 12 for the provider implementation.)

### 2. Verify config files are loaded
```bash
php artisan config:clear
php artisan config:cache
php artisan tinker --execute="config('workcore.vertical');"
# Expected: 'cleaning'
php artisan tinker --execute="config('workcore.features.crm');"
# Expected: true
```

### 3. Verify VerticalLanguageResolver binding
```bash
php artisan tinker --execute="app(App\Services\VerticalLanguageResolver::class)->label('sites');"
# Expected: 'Jobs'
php artisan tinker --execute="workcore_label('service_jobs');"
# Expected: 'Cleaning Checklist'
```

### 4. Verify route modularisation
```bash
php artisan route:list --name=dashboard.crm | head -20
php artisan route:list --name=dashboard.work | head -20
php artisan route:list --name=dashboard.money | head -20
php artisan route:list --name=dashboard.team | head -20
php artisan route:list --name=dashboard.insights | head -20
php artisan route:list --name=dashboard.support | head -20
```
Expected: All core routes load without errors. No 500s on `route:cache`.

### 5. Check AppServiceProvider snippets
Verify these are present (add if missing — see prompt 12):
- Cashier ignores migrations
- HTTPS force in production
- CarbonInterval formatHuman macro

### 6. Verify scheduler registration
```bash
php artisan schedule:list
```
Expected output includes: `agreements:run-scheduled` (hourly)

---

## Phase 15 — Full Validation Checklist

### Step 1: Dependencies
```bash
composer install --ignore-platform-req=ext-redis
composer validate
php artisan config:cache
php artisan route:cache
```

### Step 2: Database
```bash
php artisan migrate --dry-run 2>&1 | grep -E "error|Error|FAIL"
# If no errors:
php artisan migrate
php artisan db:seed --class=MenuSeeder
```

### Step 3: Run test suite
```bash
php artisan test --stop-on-failure 2>&1 | tail -30
```

For each failing test:
1. Read the failure output
2. Fix the underlying code issue
3. Re-run the specific test: `php artisan test --filter=TestName`
4. Document any tests blocked by missing infrastructure

### Step 4: Manual panel validation checklist
Work through each item and mark ✅ or ❌:

**CRM**
- [ ] Create a customer
- [ ] Create an enquiry linked to the customer
- [ ] Convert enquiry to quote
- [ ] Add contacts/notes/documents to customer

**Work**
- [ ] Create a site (job location)
- [ ] Create a service job linked to the site
- [ ] Add checklist items to the service job
- [ ] Mark attendance check-in/check-out
- [ ] Create a shift and assign to a cleaner
- [ ] Submit a leave request

**Money**
- [ ] Create a quote with line items
- [ ] Mark quote as accepted
- [ ] Convert quote to invoice
- [ ] Record a payment on the invoice
- [ ] Invoice balance reaches zero → status = paid
- [ ] InvoicePaid event fires → LiveNotification received

**Insights**
- [ ] Overview page loads with real company data
- [ ] Counts match created records (not hardcoded demo data)

**Support**
- [ ] Create a support ticket
- [ ] Admin replies — status flips to waiting_on_user
- [ ] User replies — status flips to waiting_on_team
- [ ] Mark resolved

**General**
- [ ] Menu shows correct items per cleaning vertical labels
- [ ] No 404s on any menu link
- [ ] company_id scoping: log in as user from Company A, cannot see Company B's data
- [ ] Assets compile: `npm run build` completes without errors

### Step 5: Create Validation Report
Create `docs/MERGE_VALIDATION_REPORT.md`:
```markdown
# Merge Validation Report
Date: [today]
Environment: [local/staging]

## Results
| Check | Status | Notes |
|-------|--------|-------|
| composer install | ✅/❌ | |
| migrations | ✅/❌ | |
| route:cache | ✅/❌ | |
| test suite | ✅/❌ | X passing, Y failing |
| CRM workflows | ✅/❌ | |
| Work workflows | ✅/❌ | |
| Money workflows | ✅/❌ | |
| Insights | ✅/❌ | |
| Support | ✅/❌ | |
| Menu/nav | ✅/❌ | |
| Tenancy isolation | ✅/❌ | |
| Assets build | ✅/❌ | |

## Blockers
[List any remaining blockers]

## Definition of Done Status
[Yes/No — per WORKCORE_MERGE.md definition]
```
