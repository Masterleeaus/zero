# docs/ROUTE_LOAD_GRAPH.md

**Phase 7 — Route Load Graph**
**Date:** 2026-04-03
**Scope:** Complete picture of how routing enters the system from boot to request dispatch.

---

## 1. Boot Entry Point

```
bootstrap/app.php
  └─ App\Http\Kernel (HttpKernel)
       └─ $app->singleton(Illuminate\Contracts\Http\Kernel::class, App\Http\Kernel::class)

config/app.php → providers[]
  └─ App\Providers\RouteServiceProvider   ← SOLE canonical route loader
```

All other providers in `config/app.php` (TitanCoreServiceProvider, WorkCoreServiceProvider,
ExtensionServiceProvider, TitanPwaServiceProvider, TitanSignalsServiceProvider,
AuthServiceProvider, EventServiceProvider, etc.) do **NOT** register routes.

---

## 2. RouteServiceProvider::boot() Execution Flow

```
RouteServiceProvider::boot()
│
├─ configureRateLimiting()
│   └─ RateLimiter::for('api', 60/min by user ID or IP)
│
├─ $this->routes(function() {
│   │
│   ├─ [1] API Group
│   │   Route::middleware('api')
│   │   ->prefix('api')
│   │   ->group(base_path('routes/api.php'))
│   │
│   └─ [2] Web Group
│       Route::middleware(['web', ViewSharedMiddleware, NewExtensionInstalled])
│       ->group(function() {
│           │
│           ├─ require routes/web.php
│           │   ├─ require routes/auth.php       (guest routes: login, register, OTP, social login)
│           │   ├─ require routes/panel.php      (dashboard.* — all admin + user panel routes)
│           │   └─ require routes/webhooks.php   (payment gateway callbacks — NO auth)
│           │
│           └─ Route::middleware(['auth', 'throttle:120,1'])
│               ->group(function() {
│                   $this->loadCoreRoutes()      ← glob loads routes/core/*.routes.php
│               })
│       })
│ })
│
└─ Route::bind() calls (custom model resolution):
    customer → App\Models\Crm\Customer
    enquiry  → App\Models\Crm\Enquiry
    site     → App\Models\Work\Site
    job      → App\Models\Work\ServiceJob
    checklist→ App\Models\Work\Checklist
    quote    → App\Models\Money\Quote
    invoice  → App\Models\Money\Invoice
    payment  → App\Models\Money\Payment
    case     → App\Extensions\TitanRewind\System\Models\RewindCase
    zone     → App\Models\Work\ServiceArea
    service_area_region   → App\Models\Work\ServiceAreaRegion
    service_area_district → App\Models\Work\ServiceAreaDistrict
    service_area_branch   → App\Models\Work\ServiceAreaBranch
```

---

## 3. Glob Loader: loadCoreRoutes()

```php
// RouteServiceProvider::loadCoreRoutes()
$files = glob('routes/core/*.routes.php');
$files = array_filter($files, fn($f) => preg_match('/^[a-z][a-z_]*\.routes\.php$/', basename($f)));
sort($files);  // ← alphabetical order determines priority
foreach ($files as $routeFile) { require $routeFile; }
```

### Files Loaded (alphabetical order, all active):

```
routes/core/
  01  crm.routes.php          dashboard.crm.*
  02  insights.routes.php     dashboard.insights.*
  03  inventory.routes.php    dashboard.inventory.*
  04  mcp.routes.php          titan.mcp.* + titan.signal.*
  05  money.routes.php        dashboard.money.*
  06  portal.routes.php       portal.service.*
  07  project.routes.php      work.projects.*
  08  pwa.routes.php          pwa.*
  09  repair.routes.php       repair.*
  10  rewind.routes.php       titanrewind.*
  11  route.routes.php        dashboard.work.routes.*
  12  signals.routes.php      (closures — no named prefix)
  13  social.routes.php       social-media.* + dashboard.user.social-media.*
  14  support.routes.php      dashboard.support.*
  15  team.routes.php         dashboard.team.*
  16  titan_admin.routes.php  admin.titan.core.*
  17  titan_core.routes.php   (status + memory API)
  18  work.routes.php         dashboard.work.*
```

**Sort-order risk:** `social.routes.php` loads after `panel.php` (panel.php is loaded via web.php,
before core routes). This means social.routes.php name definitions shadow any identically-named
routes in panel.php. This is the root cause of CRIT-05 (fal-ai name collision).

---

## 4. Middleware Inheritance Graph

Every route inherits middleware from ALL enclosing groups. This is the actual effective
middleware stack per route category:

### 4a. Standard Dashboard Routes (panel.php, crm, work, money, team, support, etc.)

```
web                     (EncryptCookies, Session, CSRF, SubstituteBindings, Locale, Theme)
ViewSharedMiddleware     (view data injection)
NewExtensionInstalled    (marketplace extension check)
auth                     (session authentication)
updateUserActivity       (last-seen timestamp)
throttle:120,1
```

### 4b. Admin Dashboard Routes (panel.php admin section)

```
+ above stack +
admin                    (AdminPermissionMiddleware — role check)
```

### 4c. MCP API Routes (mcp.routes.php)

```
web + ViewSharedMiddleware + NewExtensionInstalled   ← from RSP outer group (REDUNDANT for API)
auth                                                  ← from RSP inner group (REDUNDANT — session)
throttle:120,1                                        ← from RSP inner group (REDUNDANT)
auth:sanctum                                          ← from route file (correct API auth)
EnforceTitanTenancy                                   ← from route file
throttle:60,1                                         ← from route file
```

⚠️ MCP routes carry session middleware (`web`) and session-based `auth` even though they
   are API endpoints intended for Sanctum Bearer token auth. This is HIGH-01 in the audit.

### 4d. PWA Routes (pwa.routes.php)

```
web + ViewSharedMiddleware + NewExtensionInstalled   ← from RSP outer group (REDUNDANT for API)
auth                                                  ← from RSP inner group (REDUNDANT)
throttle:120,1                                        ← from RSP inner group (REDUNDANT)
auth:sanctum                                          ← from route file (correct)
EnforceTitanTenancy                                   ← from route file
```

### 4e. Social Media Routes (social.routes.php)

```
web                     ← from RSP outer group
ViewSharedMiddleware
NewExtensionInstalled
auth                    ← from RSP inner group
throttle:120,1          ← from RSP inner group
web                     ← from social.routes.php (DUPLICATE)
auth                    ← from social.routes.php (DUPLICATE)
```

⚠️ `web` and `auth` are applied twice. Laravel deduplicates middleware in some cases,
   but this is architecturally incorrect and masks intent.

### 4f. Portal Routes (portal.routes.php)

```
web + ViewSharedMiddleware + NewExtensionInstalled   ← from RSP outer group
auth                                                  ← from RSP inner group
throttle:120,1                                        ← from RSP inner group
(NO updateUserActivity)                               ← missing vs standard dashboard
```

### 4g. Webhook Routes (webhooks.php)

```
web                     ← from RSP outer group
ViewSharedMiddleware
NewExtensionInstalled
(NO auth)               ← webhooks are public callback endpoints
(NO throttle)
```

### 4h. Public Web Routes (web.php, auth.php)

```
web + ViewSharedMiddleware + NewExtensionInstalled
guest                   ← for login/register routes
checkInstallation       ← for root index route
```

---

## 5. Controller Resolution Path

```
Route match
  └─ Controller class resolved via Laravel IoC container
       └─ If class missing → BindingResolutionException (500 error)
       └─ If method missing → ActionNotFoundException (500 error)
       └─ If custom Route::bind() → model query → 404 if not found
```

---

## 6. Extension Route Injection Risk

Currently no extensions inject routes via their own service providers. All routes are
file-based and loaded by the RSP.

**However, the glob loader has no exclusion list.** Any `.routes.php` file matching
`/^[a-z][a-z_]*\.routes\.php$/` placed in `routes/core/` will be silently loaded.

Dormant risk: If a future extension provider drops a route file into `routes/core/` as
part of installation, it will be auto-loaded without explicit consent.

---

## 7. Route Cache Considerations

`php artisan route:cache` compiles all routes to a cached file. For this to work:

- All route closures must be removed (see `signals.routes.php` which uses closures — LOW-08)
- All controller classes referenced must exist (currently blocked by missing SocialMedia extension)
- Route cache must be cleared after any route file change: `php artisan route:clear`

**Current status:** Route cache is **blocked** by missing SocialMedia extension controllers.
Running `php artisan route:cache` will fail until CRIT-04 is resolved.
