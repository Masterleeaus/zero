# docs/PHASE7_FIX_PLAN.md

**Phase 7 — Recommended Ordered Repair Plan**
**Date:** 2026-04-03
**Basis:** Findings in ROUTE_SYSTEM_AUDIT.md, CONTROLLER_ROUTE_INTEGRITY.md,
MIDDLEWARE_ROUTE_AUDIT.md, ROUTE_RENAME_RISK_MAP.md, ROUTE_LOAD_GRAPH.md.

---

> **IMPORTANT:** This document is the ONLY place where fixes are proposed.
> Do not implement any changes until the correct phase sequence is confirmed.
> Each phase must be completed and validated before the next phase begins.

---

## Overview

| Phase | Title | Risk | Files Touched |
|-------|-------|------|--------------|
| 8A | Critical collision fixes in webhooks.php | Low | 1 file, 2 lines |
| 8B | Critical Instagram webhook name fix | Low | 1 file, 1 line |
| 8C | Resolve missing SocialMedia extension controllers | High | social.routes.php + extension install |
| 8D | MCP / PWA middleware group migration | Medium | mcp.routes.php, pwa.routes.php, api.php |
| 8E | Social routes middleware de-duplication | Low | social.routes.php |
| 8F | Missing updateUserActivity on portal + project routes | Low | portal.routes.php, project.routes.php |
| 8G | Webhook signature validation gap | Medium | webhooks.php |
| 8H | Panel.php rate limiting gap | Low | panel.php |
| 8I | Route naming normalisation (non-dashboard prefixes) | Medium | 5 route files + blade views |
| 8J | Signal closure extraction to controller | Low | signals.routes.php |
| 8K | Throttle config key unification | Low | 5 core route files |

---

## PHASE 8A — Critical: Webhook Route Name Collisions

**Priority:** P0 — Fix immediately. No dependencies. 2-line change.

**Issues:** CRIT-01, CRIT-02

**File:** `routes/webhooks.php`

**Current (broken):**
```php
// Line 11: subscription success
Route::any('stripe/{subscription}/success', [PaymentProcessController::class, 'stripeSuccess'])
    ->name('stripe.success');   // COLLISION

// Line 14: prepaid success — shadows line 11
Route::any('stripe/{plan}/{user}/success/prepaid', [PaymentProcessController::class, 'prepaidStripeSuccess'])
    ->name('stripe.success');   // SHADOWS LINE 11 — THIS WINS

// Line 12: subscription cancel
Route::any('stripe/{subscription}/cancel', [PaymentProcessController::class, 'stripeCancel'])
    ->name('stripe.cancel');    // COLLISION

// Line 15: prepaid cancel — shadows line 12
Route::any('stripe/cancel/prepaid', [PaymentProcessController::class, 'stripeCancel'])
    ->name('stripe.cancel');    // SHADOWS LINE 12 — THIS WINS
```

**Fix:**
```php
// Line 11: subscription success
Route::any('stripe/{subscription}/success', [PaymentProcessController::class, 'stripeSuccess'])
    ->name('stripe.subscription.success');   // RENAMED

// Line 14: prepaid success
Route::any('stripe/{plan}/{user}/success/prepaid', [PaymentProcessController::class, 'prepaidStripeSuccess'])
    ->name('stripe.prepaid.success');        // RENAMED

// Line 12: subscription cancel
Route::any('stripe/{subscription}/cancel', [PaymentProcessController::class, 'stripeCancel'])
    ->name('stripe.subscription.cancel');    // RENAMED

// Line 15: prepaid cancel
Route::any('stripe/cancel/prepaid', [PaymentProcessController::class, 'stripeCancel'])
    ->name('stripe.prepaid.cancel');         // RENAMED
```

**Validation:**
1. Search codebase for `route('webhooks.stripe.success'` and `route('webhooks.stripe.cancel'`
2. Update any references to use the new names
3. Verify that Stripe dashboard is NOT using the generated URL (Stripe uses raw URIs, not
   named routes — so the URI itself does not change)

**Risk:** Low. The URI paths are unchanged. Only the name aliases change.

**Blocked by:** Nothing. Safe to implement immediately.

---

## PHASE 8B — Critical: Instagram Webhook Name Fix

**Priority:** P0 — Fix immediately. 1-line change.

**Issue:** CRIT-03

**File:** `routes/core/social.routes.php` (line 53)

**Current (broken):**
```php
Route::any('social-media/webhook/instagram', [SocialInstagramController::class, 'webhook'])
    ->name('social-media.oauth.webhook.facebook')   // WRONG — instagram route has facebook name
    ->withoutMiddleware('auth');
```

**Fix:**
```php
Route::any('social-media/webhook/instagram', [SocialInstagramController::class, 'webhook'])
    ->name('social-media.oauth.webhook.instagram')   // CORRECTED
    ->withoutMiddleware('auth');
```

**Validation:**
- Search for `route('social-media.oauth.webhook.instagram'` in codebase to confirm no
  existing references (none expected — extension is not installed)
- Instagram webhook URL registered in Meta dashboard uses the URI, not the Laravel name

**Risk:** Low. Extension is not installed; no active references to this name exist.

**Note:** This fix is trivially safe but CRIT-04 (missing controllers) must also be
resolved before Instagram webhooks actually function.

---

## PHASE 8C — Critical: Missing SocialMedia Extension Controllers

**Priority:** P1 — Cannot be deferred past next deployment.

**Issue:** CRIT-04, CRIT-05

**Files:** `routes/core/social.routes.php`

**Problem:** Three extension namespaces (`App\Extensions\SocialMedia`,
`App\Extensions\AISocialMedia`, `App\Extensions\SocialMediaAgent`) are referenced
but their directories do not exist in `app/Extensions/`.

**Options (choose one):**

### Option A — Install the extensions
Locate and install the SocialMedia, AISocialMedia, and SocialMediaAgent extension packages
into `app/Extensions/`. This is the correct long-term solution if these features are needed.

### Option B — Stub the controllers (temporary)
Create stub controller classes in `app/Extensions/SocialMedia/System/Http/Controllers/`
that return 404 or a "coming soon" response. Allows route caching to succeed.

### Option C — Disable social.routes.php until extensions are installed
Move `social.routes.php` to `routes/core/disabled/social.routes.php` or rename it
(e.g., `social.routes.php.inactive`) so the glob loader skips it.

**Recommended:** Option C as immediate fix, Option A as follow-up when extensions are ready.

**Also fix CRIT-05 (fal-ai duplicate):**

In `social.routes.php`, the FAL-AI settings route shadows the panel.php version.
The social extension's `FalAISettingController` does not exist.

```php
// REMOVE these two lines from social.routes.php:
Route::get('fal-ai', [FalAISettingController::class, 'index'])->name('fal-ai');
Route::post('fal-ai', [FalAISettingController::class, 'update'])->name('fal-ai.update');
```

If the social extension needs its own FAL-AI settings, the route name must be different
(e.g., `dashboard.admin.social-media.settings.fal-ai`).

**Risk:** Medium. Requires coordination with extension deployment plan.

---

## PHASE 8D — High: MCP / PWA Routes — Move to API Middleware Group

**Priority:** P1

**Issues:** HIGH-01, HIGH-02

**Files:** `routes/core/mcp.routes.php`, `routes/core/pwa.routes.php`, `routes/api.php`

**Problem:** MCP and PWA endpoints are API-style (Bearer token Sanctum) but loaded inside
the web session group via RSP. They inherit unnecessary web middleware (session, CSRF, cookies,
theme, locale).

**Fix plan:**
1. Move route definitions from `routes/core/mcp.routes.php` → `routes/api.php`
2. Move route definitions from `routes/core/pwa.routes.php` → `routes/api.php`
3. Remove the two files from `routes/core/` (or keep as stubs with a comment)
4. Remove the outer `Route::middleware(['auth', 'throttle:120,1'])` wrapping that RSP applies
   — the moved routes will get the `api` middleware group instead

**Alternative (less disruptive):** Keep files in `routes/core/` but add a
`->withoutMiddleware(['web', 'auth'])` guard and rely on `auth:sanctum` only.
This is a workaround and does not cleanly separate API from web routes.

**Risk:** Medium. Functional change — test all MCP and PWA endpoints after migration.

**Validation:**
- `php artisan route:list --path=api/titan/mcp` must show only `auth:sanctum` middleware
- `php artisan route:list --path=api/pwa` must show only `auth:sanctum` middleware
- No session cookies required on MCP/PWA endpoints after fix

---

## PHASE 8E — High: Social Routes Middleware De-duplication

**Priority:** P2

**Issue:** HIGH-03

**File:** `routes/core/social.routes.php`

**Problem:** The outer `Route::middleware(['web', 'auth'])` group in social.routes.php
re-declares both `web` and `auth` which are already applied by RSP's outer and inner groups.

**Fix:**
Remove the outer `Route::middleware(['web', 'auth'])->group(function() {` wrapper from
social.routes.php. Routes that need to bypass auth (webhook callbacks) should use
`->withoutMiddleware('auth')` individually — as they already do.

**Risk:** Low (after CRIT-04 is resolved). Social extension must be installed to test.

---

## PHASE 8F — Medium: Add Missing updateUserActivity to Portal and Project Routes

**Priority:** P2

**Issues:** MED-01, MED-02

**Files:** `routes/core/portal.routes.php`, `routes/core/project.routes.php`

**Fix for portal.routes.php:**
```php
// Before
Route::prefix('portal/service')->name('portal.service.')->group(function () {

// After
Route::middleware(['updateUserActivity'])->prefix('portal/service')->name('portal.service.')->group(function () {
```

**Fix for project.routes.php:**
```php
// Before
Route::prefix('dashboard/work/projects')->name('work.projects.')->group(function () {

// After
Route::middleware(['updateUserActivity'])->prefix('dashboard/work/projects')->name('work.projects.')->group(function () {
```

**Risk:** Low. Non-breaking addition. No route names or URIs change.

---

## PHASE 8G — High: Webhook Signature Validation

**Priority:** P1 (security)

**Issue:** HIGH-04

**File:** `routes/webhooks.php`

**Problem:** Payment webhook endpoints have no request signature validation. Stripe, Paddle,
and other gateways sign their payloads. Without verification, spoofed webhook calls
could trigger payment processing logic.

**Fix:**
Create or apply a `VerifyWebhookSignature` middleware that validates the gateway-specific
signature header before dispatching to `PaymentProcessController`.

```php
// routes/webhooks.php
Route::prefix('webhooks')
    ->name('webhooks.')
    ->middleware(['throttle:60,1'])  // Add rate limiting
    ->group(function () {
        Route::match(['get', 'post'], '/{gateway}', [PaymentProcessController::class, 'handleWebhook'])
            ->middleware('verify.webhook.signature'); // Add signature check
        // ...
    });
```

**Note:** The middleware class `App\Http\Middleware\VerifyWebhookSignature` may already
exist — verify before creating. The exact implementation depends on gateway requirements.

**Risk:** Medium. Functional change to payment flow — requires thorough testing.

---

## PHASE 8H — Low: Panel.php Rate Limiting Gap

**Priority:** P3

**Issue:** MED-09

**File:** `routes/panel.php`

**Problem:** Panel routes (loaded via web.php, outside the RSP inner throttle group) have
no rate limiting. Only the core routes inner group applies `throttle:120,1`.

**Fix:**
Add explicit `throttle:120,1` to the outer group in panel.php:

```php
// Before
Route::middleware(['auth', 'updateUserActivity'])->prefix('dashboard')->as('dashboard.')->group(function () {

// After
Route::middleware(['auth', 'updateUserActivity', 'throttle:120,1'])->prefix('dashboard')->as('dashboard.')->group(function () {
```

**Risk:** Low. Non-breaking addition.

---

## PHASE 8I — Route Naming Normalisation

**Priority:** P3 — Deferred until all critical fixes are complete.

**Issues:** MED-03, MED-04, MED-05

**⚠️ Do NOT implement until all prior phases are complete.**

| Current Prefix | Target Prefix | Route File | Blade Views to Update | Controller Redirects to Update |
|---------------|--------------|-----------|----------------------|-------------------------------|
| `repair.*` | `dashboard.repair.*` | repair.routes.php | ~12 view files in resources/views/default/panel/user/repair/ | RepairOrderController, RepairTemplateController |
| `titanrewind.*` | `dashboard.user.titanrewind.*` | rewind.routes.php | ~15 view files in resources/views/default/panel/user/titanrewind/ | TitanRewindCaseController |
| `admin.titan.core.*` | `dashboard.admin.titan.core.*` | titan_admin.routes.php | ~20 view files in resources/views/default/panel/admin/titancore/ | TitanCoreAdminController |
| `portal.service.*` | `dashboard.portal.service.*` | portal.routes.php | ~5 view files | PortalController |
| `work.projects.*` | `dashboard.work.projects.*` | project.routes.php | ~3 view files | FieldServiceProjectController |

**Migration approach for each prefix rename:**
1. Add new name alongside old name (both work simultaneously)
2. Update all Blade views to use new name
3. Update all controller redirects to use new name
4. Verify no remaining references to old name
5. Remove old name

**Risk:** Medium. Must be done file-by-file with validation at each step.

---

## PHASE 8J — Signal Route Closure Extraction

**Priority:** P3

**Issue:** Low-08 (signals closures block route:cache)

**File:** `routes/core/signals.routes.php`

**Fix:**
Create `App\Http\Controllers\Core\Signals\TitanSignalsController` with `index()` and
`envelope()` methods, then replace the closures in signals.routes.php.

**Risk:** Low. Enables `php artisan route:cache` optimisation.

---

## PHASE 8K — Throttle Config Key Unification

**Priority:** P3

**Issue:** LOW-05, LOW-06

**Files:** work.routes.php, team.routes.php, inventory.routes.php, route.routes.php, support.routes.php

**Fix:**
Standardise all core route files to use a single config key, e.g.:

```php
// Agree on one standard:
config('throttle.dashboard', '120,1')
// OR hardcode:
'throttle:120,1'
```

Update all inconsistent files to use the agreed standard.

**Risk:** Low. No functional change.

---

## Implementation Order Summary

```
IMMEDIATE (no dependencies, safe to do today):
  8A  →  Webhook stripe name collision fix         (2 lines in webhooks.php)
  8B  →  Instagram webhook name fix                (1 line in social.routes.php)

SHORT-TERM (P1, before next release):
  8C  →  Disable social.routes.php + remove fal-ai duplicate
  8G  →  Webhook signature validation middleware
  8D  →  MCP + PWA route group migration to api

MEDIUM-TERM (P2, next sprint):
  8E  →  Social routes middleware de-duplication (after 8C/extension install)
  8F  →  Add updateUserActivity to portal + project routes
  8H  →  Panel.php rate limiting gap

LONG-TERM (P3, controlled migration sprint):
  8I  →  Route naming normalisation (repair, titanrewind, admin.titan.core, portal, work.projects)
  8J  →  Signal closure extraction to controller
  8K  →  Throttle config key unification
```

---

## Success Criteria for Phase 8

- [ ] `php artisan route:list` produces no errors
- [ ] `php artisan route:cache` succeeds (requires 8C + 8J)
- [ ] No duplicate route names in `php artisan route:list | sort | uniq -d`
- [ ] All webhook route names are unique
- [ ] Instagram webhook has correct name
- [ ] MCP routes do not carry `web` session middleware
- [ ] Portal and project routes have `updateUserActivity`
- [ ] All Stripe payment flows tested end-to-end after 8A/8B
