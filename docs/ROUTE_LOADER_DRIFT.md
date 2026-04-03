# ROUTE_LOADER_DRIFT.md

**Phase 8 — Step 4: Route Loader Drift Audit**
**Date:** 2026-04-03
**Scope:** RouteServiceProvider, routes/web.php, routes/api.php, routes/panel.php, routes/core/*.routes.php, CodeToUse route files

---

## 1. Route Loading Architecture (Current State)

```
RouteServiceProvider::boot()
├── API group: routes/api.php
└── Web group:
    ├── routes/web.php
    └── Auth+Throttle group:
        └── loadCoreRoutes() → glob('routes/core/*.routes.php')
            ├── crm.routes.php
            ├── insights.routes.php
            ├── mcp.routes.php
            ├── money.routes.php
            ├── pwa.routes.php
            ├── repair.routes.php
            ├── rewind.routes.php
            ├── route.routes.php
            ├── signals.routes.php
            ├── social.routes.php
            ├── support.routes.php
            ├── team.routes.php
            ├── titan_admin.routes.php
            ├── titan_core.routes.php
            └── work.routes.php
```

**Additionally:**
- `routes/panel.php` — exists but loading mechanism unclear (likely included via `routes/web.php`)
- `routes/auth.php` — loaded via Fortify/Jetstream or similar auth scaffolding
- `routes/webhooks.php` — purpose: external webhook endpoints

---

## 2. Glob Loader Risk Analysis

The `loadCoreRoutes()` method uses:

```php
glob($corePath . '/*.routes.php')
```

with a filter:

```php
preg_match('/^[a-z][a-z_]*\.routes\.php$/', basename($file))
```

**Findings:**

| Risk | Description |
|------|-------------|
| **AUTO-INCLUDE** | Any `.routes.php` file dropped into `routes/core/` with a valid lowercase name is automatically loaded — no explicit registration needed |
| **SORT ORDER** | Files are sorted alphabetically — route priority depends on filename ordering |
| **NO EXCLUSION LIST** | There is no mechanism to disable/exclude specific route files without deleting them |
| **SILENT DRIFT** | New route files from future integrations will be silently loaded without explicit opt-in |

---

## 3. Double-Loader Execution Risk

No double-loader execution detected in current core files. Each `.routes.php` file is included once via the sorted glob.

**However:**
- `routes/web.php` and `routes/panel.php` should be audited to ensure they do not include any `routes/core/*.routes.php` files directly.
- Providers (e.g., `TitanRewindServiceProvider`, `MarketplaceServiceProvider`) must not call `Route::group()` independently for routes already covered by `routes/core/`.

---

## 4. Extension Route Injection Risks

### 4a. TitanRewind

- Routes in `routes/core/rewind.routes.php`
- `TitanRewindServiceProvider` does NOT register routes independently
- **Status: SAFE**

### 4b. Marketplace

- `App\Domains\Marketplace\MarketplaceServiceProvider` — needs inspection
- Marketplace providers commonly auto-register routes for extension install/uninstall endpoints
- **Risk: MEDIUM** — potential double-loading if marketplace routes are also in `routes/core/`

### 4c. CodeToUse/Extensions (Not Active)

The following CodeToUse extensions contain route files that would conflict if activated:

| Extension | Route File Location | Conflict Risk |
|-----------|-------------------|---------------|
| `CheckoutRegistration` | Extension route definitions | Auth/registration routes may shadow `routes/auth.php` |
| `Canvas` | Extension route definitions | `/canvas/*` routes — no current conflict |
| `LiveCustomizer` | Extension route definitions | Possible overlap with settings routes |

### 4d. CodeToUse/Signals

`CodeToUse/Signals/titan_signal/TitanSignalBase/routes/titan_signals.php` — defines signal routes using `api.php`-style endpoints. If integrated, these may conflict with existing `routes/core/signals.routes.php`.

**Status: HIGH risk if integrated without prefix isolation.**

### 4e. CodeToUse/Voice Passes

Each Voice pass bundle contains:
- `routes/api.php` (overwritten paths)
- Extension-level route registrations in providers

**Risk:** 9+ copies of Voice routes — only ONE canonical route set should be integrated. All others are duplicates.

---

## 5. Panel / API / Web Route Shadowing

### `routes/panel.php` vs `routes/web.php`

Both exist. The relationship between `panel.php` and `web.php` should be verified to ensure `panel.php` is not loaded twice (once via RouteServiceProvider if included in web.php, and once separately).

### API Route Prefix Collisions

The following API prefixes are in use (from `routes/api.php` and core route files):
- `/api/*` — standard API group
- `/pwa/*` — PWA service worker routes
- `/mcp/*` — MCP capability routes  
- `/signals/*` — Titan Signals
- `/dashboard/*` — Web panel routes

CodeToUse bundles using `/api/` prefix could silently add routes to the API group if their route files are ever placed in `routes/core/`.

---

## 6. Named Route Duplication Risk

No named route duplicates were detected in the current `routes/core/` files (each domain uses `dashboard.<domain>.*` prefix pattern).

**At risk if CodeToUse routes are activated:**
- Signal routes in `CodeToUse/Signals/` use `titan.signal.*` naming — may overlap with `TitanSignalsServiceProvider` route names
- Voice extension routes use `chatbot.*`, `voice.*` naming — multiple competing copies

---

## 7. Summary Table

| Risk Level | Finding |
|------------|---------|
| **HIGH** | Glob loader auto-includes any new `.routes.php` in `routes/core/` — no explicit control |
| **HIGH** | CodeToUse/Signals routes would conflict with active `signals.routes.php` |
| **HIGH** | 9+ Voice route sets in CodeToUse — only one valid for integration |
| **MEDIUM** | `routes/panel.php` relationship with `routes/web.php` needs confirmation |
| **MEDIUM** | MarketplaceServiceProvider may register routes outside glob discovery |
| **LOW** | No double-loader execution detected in current core |
| **LOW** | No duplicate named routes detected in current active route files |
