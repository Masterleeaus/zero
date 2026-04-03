# docs/MIDDLEWARE_ROUTE_AUDIT.md

**Phase 7 — Middleware Route Audit**
**Date:** 2026-04-03
**Scope:** All middleware aliases defined in `app/Http/Kernel.php`, their usage across all active
route files, gaps, incorrect assignments, auth drift, and API/webhook boundary issues.

---

## 1. Defined Middleware Aliases (Kernel.php)

### Global Middleware Stack (every request)

| Class | Purpose |
|-------|---------|
| `App\Http\Middleware\TrustProxies` | Proxy header trust |
| `App\Http\Middleware\RefererMiddleware` | Referrer tracking |
| `Illuminate\Http\Middleware\HandleCors` | CORS headers |
| `App\Http\Middleware\PreventRequestsDuringMaintenance` | Maintenance mode |
| `Illuminate\Foundation\Http\Middleware\ValidatePostSize` | POST size validation |
| `App\Http\Middleware\TrimStrings` | Input trimming |
| `Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull` | Null coalescion |

### Web Middleware Group (`web`)

| Class | Purpose |
|-------|---------|
| `App\Http\Middleware\EncryptCookies` | Cookie encryption |
| `Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse` | Cookie queuing |
| `Illuminate\Session\Middleware\StartSession` | Session start |
| `Illuminate\View\Middleware\ShareErrorsFromSession` | Validation errors |
| `RachidLaasri\LaravelInstaller\Middleware\ApplicationCheckLicense` | License check |
| `RachidLaasri\LaravelInstaller\Middleware\ApplicationStatus` | Installed check |
| `App\Http\Middleware\VerifyCsrfToken` | CSRF protection |
| `Illuminate\Routing\Middleware\SubstituteBindings` | Route model binding |
| `App\Http\Middleware\Custom\LocaleMiddleware` | Locale resolution |
| `App\Http\Middleware\Custom\ThemeMiddleware` | Theme resolution |

### API Middleware Group (`api`)

| Class | Purpose |
|-------|---------|
| `Illuminate\Routing\Middleware\ThrottleRequests:api` | API rate limiting |
| `Illuminate\Routing\Middleware\SubstituteBindings` | Route model binding |

*Note: `EnsureFrontendRequestsAreStateful` is commented out — Sanctum stateless mode only.*

### Middleware Aliases (`middlewareAliases`)

| Alias | Class | In Use |
|-------|-------|--------|
| `auth` | `App\Http\Middleware\Authenticate` | ✅ Everywhere |
| `auth.basic` | `Illuminate\Auth\Middleware\AuthenticateWithBasicAuth` | Not used in routes |
| `auth.session` | `Illuminate\Session\Middleware\AuthenticateSession` | Not used in routes |
| `cache.headers` | `Illuminate\Http\Middleware\SetCacheHeaders` | Not used in routes |
| `can` | `Illuminate\Auth\Middleware\Authorize` | Not used in routes |
| `guest` | `App\Http\Middleware\RedirectIfAuthenticated` | ✅ auth.php |
| `password.confirm` | `Illuminate\Auth\Middleware\RequirePassword` | Not used |
| `signed` | `App\Http\Middleware\ValidateSignature` | Not used |
| `throttle` | `Illuminate\Routing\Middleware\ThrottleRequests` | ✅ All core routes |
| `verified` | `Illuminate\Auth\Middleware\EnsureEmailIsVerified` | Not used |
| `admin` | `App\Http\Middleware\AdminPermissionMiddleware` | ✅ Admin routes |
| `is_not_demo` | `App\Http\Middleware\DemoCheckMiddleware` | ✅ panel.php |
| `newExtensionInstalled` | `App\Domains\Marketplace\Http\Middleware\NewExtensionInstalled` | ✅ RSP outer group |

### Route Middleware (`routeMiddleware`)

| Alias | Class | In Use |
|-------|-------|--------|
| `localize` | `LaravelLocalization\LaravelLocalizationRoutes` | ✅ panel.php |
| `localizationRedirect` | `LaravelLocalization\LaravelLocalizationRedirectFilter` | Not used in routes |
| `localeSessionRedirect` | `LaravelLocalization\LocaleSessionRedirect` | Not used in routes |
| `localeCookieRedirect` | `LaravelLocalization\LocaleCookieRedirect` | Not used in routes |
| `localeViewPath` | `LaravelLocalization\LaravelLocalizationViewPath` | Not used in routes |
| `checkInstallation` | `App\Http\Middleware\CheckInstallation` | ✅ web.php (root route) |
| `custom` | `App\Http\Middleware\Custom` | Not used in routes |
| `updateUserActivity` | `App\Http\Middleware\UpdateUserActivity` | ✅ Most core routes |
| `titan.tenancy` | `App\Http\Middleware\TitanCore\EnforceTitanTenancy` | Defined but used by class reference in mcp/pwa |
| `titan.zylos.signature` | `App\Http\Middleware\TitanCore\ValidateZylosSignature` | Defined but used by class reference in mcp |
| `sentry.context` | `App\Http\Middleware\SentryContextMiddleware` | Not used in routes |
| `surveyMiddleware` | `App\Http\Middleware\SurveyMiddleware` | Not used in routes |
| `titan.mcp.throttle` | `App\Http\Middleware\Titan\McpRateLimitMiddleware` | Not used in routes |
| `titan.ai.throttle` | `App\Http\Middleware\Titan\TitanAIRateLimitMiddleware` | Not used in routes |

---

## 2. Middleware Usage by Route Group

### 2a. Standard Authenticated Dashboard (crm, work, money, team, support, inventory, route)

```
RSP outer:  web, ViewSharedMiddleware, NewExtensionInstalled
RSP inner:  auth, throttle:120,1
Route file: updateUserActivity
```

✅ Correct and consistent across: crm, money, team, support, inventory, route, work, insights.

**Throttle configuration inconsistency (LOW-05, LOW-06):**

| File | Throttle source |
|------|----------------|
| crm, money, insights, rewind | Hardcoded `throttle:120,1` |
| work, team, inventory, route | `config('throttle.dashboard', '120,1')` |
| support | `config('dashboard.throttle_middleware', 'throttle:120,1')` |

Three different config keys used for equivalent throttle values. Should be unified.

### 2b. Admin Dashboard (panel.php admin section)

```
RSP outer:  web, ViewSharedMiddleware, NewExtensionInstalled
panel.php:  auth, updateUserActivity
panel.php admin group: admin
```

✅ Correct admin gating.

⚠️ `throttle` is **not explicitly applied in panel.php** auth group — relies on
   `throttle:120,1` from RSP inner group only for core routes. Panel.php routes loaded
   via `routes/web.php` (outside the RSP inner throttle group) have **no explicit throttle**.
   However, the RSP `web` group does NOT add throttle — only the core routes inner group does.

**Finding MED-09 (NEW):** `routes/panel.php` routes are loaded via `routes/web.php` which is
outside the RSP `auth + throttle:120,1` inner group. Panel routes therefore have **no rate
limiting** applied. Only `auth` and `updateUserActivity` are applied via panel.php's own
middleware declarations.

### 2c. MCP API Routes (mcp.routes.php)

**Actual effective stack:**
```
web (session, CSRF, cookies, theme, locale)  ← RSP outer — WRONG for API
ViewSharedMiddleware                          ← RSP outer — WRONG for API
NewExtensionInstalled                         ← RSP outer — WRONG for API
auth                                          ← RSP inner — session-based, WRONG for API
throttle:120,1                                ← RSP inner — overridden below but still stacks
auth:sanctum                                  ← route file — correct Bearer token auth
EnforceTitanTenancy                           ← route file — correct
throttle:60,1                                 ← route file — correct
```

**Issues:**
- `auth` (session) + `auth:sanctum` (token) double-stack — API clients need only Sanctum
- `web` middleware adds session overhead to API endpoints
- CSRF token required on `POST /api/titan/mcp/invoke` (can fail for external API clients)

**Recommended fix:** MCP/PWA routes should be registered in the `api` middleware group
via `routes/api.php`, not in the `web` group via core route files.

### 2d. PWA Routes (pwa.routes.php)

Same issue as 2c. PWA endpoints are API-style (Bearer token Sanctum) but loaded inside
the `web` session group.

### 2e. Social Media Routes (social.routes.php)

**Effective stack for OAuth/webhook routes:**
```
web                     ← RSP outer
ViewSharedMiddleware    ← RSP outer
NewExtensionInstalled   ← RSP outer
auth                    ← RSP inner
throttle:120,1          ← RSP inner
web                     ← social.routes.php (DUPLICATE)
auth                    ← social.routes.php (DUPLICATE)
```

The Instagram + Facebook webhook routes additionally call `->withoutMiddleware('auth')` to
remove session auth — but the `auth:sanctum` alias is not involved, so this removes both
`auth` instances. This is intentional behaviour for public webhook endpoints but the
double-stack makes it fragile.

### 2f. Portal Routes (portal.routes.php)

```
web, ViewSharedMiddleware, NewExtensionInstalled  ← RSP outer
auth, throttle:120,1                              ← RSP inner
(NO updateUserActivity)                           ← missing
```

Portal users' last-activity timestamp is not updated.

### 2g. Webhook Routes (webhooks.php)

```
web, ViewSharedMiddleware, NewExtensionInstalled  ← RSP outer (via web.php)
(NO auth)                                         ← correct: public callbacks
(NO throttle beyond global)                       ← risk: no rate limiting on payment webhooks
```

⚠️ **Finding HIGH-04 (NEW):** Payment webhook endpoints (`/webhooks/{gateway}`) have no
rate limiting. An attacker could flood the webhook endpoint. Stripe and other gateways
sign their requests — a signature validation middleware should be present.

### 2h. Titan Admin Routes (titan_admin.routes.php)

```
RSP outer: web, ViewSharedMiddleware, NewExtensionInstalled
RSP inner: auth, throttle:120,1
Route file: auth, admin, updateUserActivity
```

⚠️ `auth` is applied twice (RSP inner + route file). Functionally harmless but redundant.

---

## 3. Middleware Gaps

| Route Group | Missing Middleware | Risk |
|-------------|-------------------|------|
| `portal.routes.php` | `updateUserActivity` | MED-01 |
| `project.routes.php` | `updateUserActivity` | MED-02 |
| `routes/panel.php` | No rate limiting (`throttle`) | MED-09 |
| `routes/webhooks.php` | No webhook signature validation | HIGH-04 |
| MCP routes | Should not inherit session `web` group | HIGH-01 |
| PWA routes | Should not inherit session `web` group | HIGH-02 |

---

## 4. Auth Drift

| Route Group | Auth Applied | Expected | Status |
|-------------|-------------|---------|--------|
| All core routes | `auth` (session) | `auth` (session) | ✅ |
| Admin routes | `auth` + `admin` | `auth` + `admin` | ✅ |
| MCP routes | `auth` (session) + `auth:sanctum` (token) | `auth:sanctum` only | ⚠️ Double auth |
| PWA routes | `auth` (session) + `auth:sanctum` (token) | `auth:sanctum` only | ⚠️ Double auth |
| Social oauth callbacks | `auth` removed via `withoutMiddleware` | No auth (public callback) | ✅ (fragile) |
| Social webhook callbacks | `auth` removed via `withoutMiddleware` | No auth (public webhook) | ✅ (fragile) |
| Webhook payment callbacks | No auth | No auth (Stripe signs payloads) | ⚠️ No sig validation |

---

## 5. Webhook / API Boundary Issues

| ID | Issue | File | Severity |
|----|-------|------|---------|
| HIGH-04 | Stripe webhook endpoints have no signature validation middleware | webhooks.php | HIGH |
| HIGH-01 | MCP API endpoints carry session middleware from web group | mcp.routes.php | HIGH |
| HIGH-02 | PWA API endpoints carry session middleware from web group | pwa.routes.php | HIGH |
| HIGH-03 | Social routes double-stack `web` and `auth` middleware | social.routes.php | HIGH |
| MED-10 | `titan.mcp.throttle` alias defined in Kernel but never used | Kernel.php | MEDIUM |
| MED-11 | `titan.ai.throttle` alias defined in Kernel but never used | Kernel.php | MEDIUM |
| MED-12 | `titan.tenancy` alias defined but used only by class reference (not alias) in mcp/pwa | mcp.routes.php, pwa.routes.php | LOW |
| MED-13 | `titan.zylos.signature` alias defined but used only by class reference in mcp | mcp.routes.php | LOW |

---

## 6. Aliases Defined but Unused in Routes

| Alias | Action Recommended |
|-------|-------------------|
| `auth.basic` | Keep (Laravel standard) |
| `auth.session` | Keep (Laravel standard) |
| `cache.headers` | Keep (Laravel standard) |
| `can` | Keep (Laravel standard) |
| `password.confirm` | Keep (Laravel standard) |
| `signed` | Keep (Laravel standard) |
| `verified` | Keep — may be needed for email verification flow |
| `localizationRedirect` | Low priority — may be used via package config |
| `localeSessionRedirect` | Low priority — may be used via package config |
| `localeCookieRedirect` | Low priority — may be used via package config |
| `localeViewPath` | Low priority — may be used via package config |
| `custom` | Evaluate — class exists but no route usage found |
| `sentry.context` | Should be added to admin/user dashboard groups for error tracking |
| `surveyMiddleware` | Evaluate — no route usage found |
| `titan.mcp.throttle` | Should replace hardcoded `throttle:60,1` on MCP routes |
| `titan.ai.throttle` | Should be added to AI generation routes in panel.php |
| `titan.tenancy` | Should replace class reference in mcp/pwa routes |
| `titan.zylos.signature` | Should replace class reference in mcp signal callback |

---

## 7. Recommended Middleware Fix Actions

| Priority | Action | Affected Files |
|----------|--------|----------------|
| P0 | Add Stripe signature validation to webhook routes | webhooks.php |
| P1 | Move MCP routes out of web group into api group (routes/api.php or separate api routes) | mcp.routes.php |
| P1 | Move PWA routes out of web group into api group | pwa.routes.php |
| P1 | Remove duplicate `web` + `auth` in social.routes.php outer group | social.routes.php |
| P2 | Add `updateUserActivity` to portal.routes.php and project.routes.php | portal.routes.php, project.routes.php |
| P2 | Standardise throttle config key across all core route files | All core/*.routes.php |
| P3 | Replace class references with aliases in mcp/pwa routes | mcp.routes.php, pwa.routes.php |
| P3 | Evaluate adding `sentry.context` to dashboard groups | panel.php |
