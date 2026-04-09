# AUDIT REPORT — Titan Zero
**Date:** 2025-07-24  
**Auditor:** GitHub Copilot Agent

---

## 1. INSTALL READINESS VERDICT

⚠️ **CONDITIONALLY INSTALLABLE** — Core framework boots with minor fixes. Two route-level bugs confirmed and one fixed. Social Media extensions are missing entirely (orphaned route file). All other service providers, config files, models, events, and listeners are present.

---

## 2. STACK INVENTORY

| Item | Value |
|------|-------|
| Framework | Laravel ^10.0 |
| PHP required | ^8.2 |
| DB default | MySQL (DB_CONNECTION=mysql) |
| Queue default | sync |
| Asset pipeline | Vite (vite.config.mjs) + Tailwind |
| Auth | Sanctum + Passport + Socialite |
| Key packages | Livewire 3, Spatie Permission, Cashier, Octane, Sentry |
| Local packages | magicai-updater, magicai-healthy, openai-php, rachidlaasri/laravel-installer |

---

## 3. CRITICAL BLOCKERS

### ✅ FIXED — Wrong DispatchController namespace in work.routes.php
**File:** `routes/core/work.routes.php` lines 211–214  
**Bug:** Routes referenced `\App\Http\Controllers\Work\DispatchController::class` (does not exist).  
**Fix:** Replaced with `DispatchController::class` which uses the correct `use` import already at line 3 (`App\Http\Controllers\Core\Work\DispatchController`).  
**Status:** Fixed in-place.

### ⚠️ ACTIVE — Orphaned social.routes.php with missing extension controllers
**File:** `routes/core/social.routes.php`  
**Bug:** References 31 controller/middleware classes from three missing extensions:
- `App\Extensions\SocialMedia\System\*` — entire extension absent
- `App\Extensions\AISocialMedia\System\*` — entire extension absent
- `App\Extensions\SocialMediaAgent\System\*` — entire extension absent

**Impact:** Route class resolution fails at runtime when those routes are hit. Does NOT crash boot (PHP `use` + `::class` are not eagerly autoloaded). But any request to a social route 500s immediately.  
**Fix needed:** Either install the extensions from CodeToUse or comment out social.routes.php until extensions are present.

---

## 4. MAJOR BUGS / WIRING ISSUES

| # | Severity | File | Issue |
|---|----------|------|-------|
| 1 | HIGH | `routes/core/work.routes.php` | DispatchController namespace wrong (**FIXED**) |
| 2 | HIGH | `routes/core/social.routes.php` | 3 missing extensions (31 controllers/middleware) |
| 3 | MEDIUM | `config/throttle.php` | Missing — 5 route files call `config('throttle.dashboard', '120,1')` with a default, so non-fatal, but config key is undeclared |
| 4 | LOW | `app/Providers/EventServiceProvider.php` | 91 events, `shouldDiscoverEvents()=false` — manual maintenance burden, no boot risk |

---

## 5. WHAT IS HEALTHY

- **All 17 ServiceProviders** in `config/app.php` have their PHP files present
- **All config files** referenced by ServiceProviders exist (`workcore`, `verticals`, `titan_core`, `titan_ai`, `titan_budgets`, `titan_omni`, `titan_process`, `titan_memory`, `titan_signal`, `titan-rewind`, `admin`, `pwa`)
- **All autoload files** exist (`helpers.php`, `AdsenseService.php`, `workcore_helpers.php`)
- **All morph-mapped models** exist (`Work/ServiceJob`, `Route/DispatchRoute`, `Work/Shift`)
- **All route-bound models** exist (Customer, Enquiry, Site, Checklist, Quote, Invoice, Payment, ServiceArea, ServiceAreaRegion/District/Branch)
- **All Kernel middleware** classes resolved (0 missing)
- **All 256 event classes** and **73 listener classes** present
- **No duplicate migration timestamps** found (517 migrations)
- **TitanCore, TitanRewind, Entity, Engine, Marketplace** domains structurally intact
- **ContractController** (`App\Http\Controllers\Work`) is correctly placed and referenced

---

## 6. FILES CHANGED

| File | Change |
|------|--------|
| `routes/core/work.routes.php` | Fixed lines 211–214: `\App\Http\Controllers\Work\DispatchController::class` → `DispatchController::class` |

---

## 7. EXACT INSTALL STEPS

```bash
# 1. Copy env
cp .env.example .env

# 2. Fill in .env
#    APP_KEY=  (generate below)
#    DB_HOST / DB_DATABASE / DB_USERNAME / DB_PASSWORD

# 3. Install PHP deps
composer install --no-interaction --prefer-dist --optimize-autoloader

# 4. Generate key
php artisan key:generate

# 5. Run migrations
php artisan migrate --force

# 6. Install JS deps + build assets
npm install && npm run build

# 7. Seed (optional)
php artisan db:seed

# 8. Cache for production
php artisan config:cache && php artisan route:cache && php artisan view:cache

# 9. Queue worker (if not using sync)
php artisan queue:work
```

---

## 8. TOP PRIORITY NEXT FIXES

1. **Install or stub social media extensions** — `SocialMedia`, `AISocialMedia`, `SocialMediaAgent` are referenced in `social.routes.php` but absent. Either bring in from `CodeToUse/` or wrap route file with existence check.
2. **Create `config/throttle.php`** — add `dashboard` key to make the config call explicit rather than relying on default fallback in 5 route files.
3. **Audit remaining core route files** for any other controller namespace mismatches similar to the DispatchController bug.
4. **Enable auto-discovery or prune EventServiceProvider** — 91 manually listed events/listeners is brittle; move to `shouldDiscoverEvents()=true` or split into domain event providers.
5. **Verify packages/ local path repos** are present and have valid `composer.json` before running `composer install`.

