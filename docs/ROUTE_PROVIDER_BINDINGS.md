# TitanZero — Route Provider Bindings

> Static scan of all Service Providers for route registration activity.

---

## Summary Table

| Provider | File | Registers Routes? | Route Files | Double-Load Risk |
|----------|------|------------------|-------------|-----------------|
| `RouteServiceProvider` | `app/Providers/RouteServiceProvider.php` | ✅ Yes | `routes/api.php`, `routes/web.php` (→ chains `auth.php`, `panel.php`, `webhooks.php`), `routes/core/*.routes.php` | None |
| `BroadcastServiceProvider` | `app/Providers/BroadcastServiceProvider.php` | ✅ Yes (channels) | `routes/channels.php` | None |
| `ExtensionServiceProvider` | `app/Providers/ExtensionServiceProvider.php` | ❌ No | Delegates to `ChatbotServiceProvider` only | N/A |
| `TitanCoreServiceProvider` | `app/Providers/TitanCoreServiceProvider.php` | ❌ No | None | N/A |
| `TitanPwaServiceProvider` | `app/Providers/TitanPwaServiceProvider.php` | ❌ No | None | N/A |
| `TitanSignalsServiceProvider` | `app/Providers/TitanSignalsServiceProvider.php` | ❌ No | None | N/A |
| `WorkCoreServiceProvider` | `app/Providers/WorkCoreServiceProvider.php` | ❌ No | None | N/A |

---

## Detailed Provider Notes

---

### `RouteServiceProvider`

**Path:** `app/Providers/RouteServiceProvider.php`  
**Extends:** `Illuminate\Foundation\Support\Providers\RouteServiceProvider`

#### Route registration

```php
$this->routes(function () {
    Route::middleware('api')
        ->prefix('api')
        ->group(base_path('routes/api.php'));

    Route::middleware(['web', ViewSharedMiddleware::class, NewExtensionInstalled::class])
        ->group(function () {
            require base_path('routes/web.php');

            Route::middleware(['auth', 'throttle:120,1'])->group(function () {
                $this->loadCoreRoutes();
            });
        });
});
```

#### `loadCoreRoutes()`

- Globs `routes/core/*.routes.php`
- Filters to names matching `/^[a-z][a-z_]*\.routes\.php$/`
- Sorts files alphabetically then `require`s each

**Files currently loaded (sorted):**

1. `crm.routes.php`
2. `insights.routes.php`
3. `mcp.routes.php`
4. `money.routes.php`
5. `pwa.routes.php`
6. `rewind.routes.php`
7. `signals.routes.php`
8. `social.routes.php`
9. `support.routes.php`
10. `team.routes.php`
11. `titan_admin.routes.php`
12. `titan_core.routes.php`
13. `work.routes.php`

#### Dynamic extension route loading

Two glob-based loaders exist but their target directories are **empty**:

| Glob Pattern | Directory Exists | Files Found |
|-------------|-----------------|-------------|
| `routes/extroutes/*.php` | ❌ No | 0 (panel.php) |
| `routes/extapiroutes/*.php` | ❌ No | 0 (api.php) |

These are silent no-ops. No double-load risk.

#### Route bindings registered

See `ROUTE_MANIFEST_FULL.md §3` for the full list of `Route::bind()` calls.

#### Rate limiter

```php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

---

### `BroadcastServiceProvider`

**Path:** `app/Providers/BroadcastServiceProvider.php`  
**Registers:** `routes/channels.php` via `require`.  
No route (HTTP) registration — channel authorization only.

---

### `ExtensionServiceProvider`

**Path:** `app/Providers/ExtensionServiceProvider.php`

Iterates `extensionProviders()` array and registers each via `$this->app->register()`.  
Currently the only entry is `ChatbotServiceProvider`.

Does **not** directly register any HTTP routes. Sub-providers may register routes but `ChatbotServiceProvider` was not in scope for this scan.

**No double-load risk** from this provider.

---

### `TitanCoreServiceProvider`

**Path:** `app/Providers/TitanCoreServiceProvider.php`

Registers config merges and ~35 singleton bindings for the Titan AI kernel, memory, tool registry, and agent studio subsystems.

**`boot()` method is empty — no routes registered.**

---

### `TitanPwaServiceProvider`

**Path:** `app/Providers/TitanPwaServiceProvider.php`

Registers ~7 singletons for the PWA manifest, sync, signature validation, and consensus systems.

**`boot()` method is empty — no routes registered.**

> PWA routes are loaded via `routes/core/pwa.routes.php` (part of the `loadCoreRoutes()` glob), not from this provider.

---

### `TitanSignalsServiceProvider`

**Path:** `app/Providers/TitanSignalsServiceProvider.php`

Registers ~13 singletons including `SignalsService`, `SignalDispatcher`, `ProcessRecorder`, and subscriber classes.

**`boot()` method is empty — no routes registered.**

> Signal API routes are in `routes/api.php` (inline) and `routes/core/signals.routes.php` (user dashboard). Neither is loaded from this provider.

---

### `WorkCoreServiceProvider`

**Path:** `app/Providers/WorkCoreServiceProvider.php`

Registers `VerticalLanguageResolver` singleton and merges `workcore` and `verticals` config files.

**`boot()` method is empty — no routes registered.**

> Work domain routes are in `routes/core/work.routes.php`.

---

## Double-Loading Issues

### Issue 1 — `fal-ai` settings routes registered twice

| File | Full Route Name | Handler |
|------|----------------|---------|
| `routes/panel.php` | `dashboard.admin.settings.fal-ai` | `Common\Settings\FalAISettingController@index` |
| `routes/core/social.routes.php` | `dashboard.admin.settings.fal-ai` | `Extensions\SocialMedia\...\FalAISettingController@index` |
| `routes/panel.php` | `dashboard.admin.settings.fal-ai.update` | `Common\Settings\FalAISettingController@update` |
| `routes/core/social.routes.php` | `dashboard.admin.settings.fal-ai.update` | `Extensions\SocialMedia\...\FalAISettingController@update` |

**Effect:** `social.routes.php` is loaded *after* `panel.php` (core routes load after web routes in RSP). The social.routes.php definitions will silently shadow the panel.php definitions for route name lookups. Both URIs will still resolve but `route('dashboard.admin.settings.fal-ai')` will point to the social extension handler.

**Note:** The social extension controller class is currently missing from disk, so the shadowing handler will throw a `BindingResolutionException` at runtime.

### Issue 2 — `webhooks.stripe.success` / `webhooks.stripe.cancel` registered twice (within `webhooks.php`)

Both names are registered twice within the same file under the same `webhooks.` prefix. The second registration shadows the first for name-based lookups.

See `ROUTE_NAME_COLLISIONS.md` for full classification and recommended resolution.

### Issue 3 — `social-media.oauth.webhook.facebook` registered twice (within `social.routes.php`)

Both the Instagram webhook route and the Facebook webhook route use the name `social-media.oauth.webhook.facebook`. The second registration (Facebook) shadows the first (Instagram) for name lookups.

### Issue 4 — `verify-otp` registered twice (within `auth.php`)

Both GET and POST to `verify-otp` share the same route name. In Laravel, the last-registered route wins for name lookup. The POST handler (`verifyOtp`) will be returned by `route('verify-otp')`.

---

## Load Order Summary

```
bootstrap/app.php
  └─ RouteServiceProvider::boot()
       ├─ api.php          [api middleware, /api prefix]
       └─ web.php          [web + ViewSharedMiddleware + NewExtensionInstalled]
            ├─ auth.php    [required inline]
            ├─ panel.php   [required inline]
            │   ├─ extroutes/*.php  [glob — dir missing, no-op]
            │   └─ custom_routes_panel.php  [conditional — missing, no-op]
            └─ webhooks.php [required inline]
  └─ loadCoreRoutes()      [auth + throttle:120,1 wrapper]
       ├─ crm.routes.php
       ├─ insights.routes.php
       ├─ mcp.routes.php
       ├─ money.routes.php
       ├─ pwa.routes.php
       ├─ rewind.routes.php
       ├─ signals.routes.php
       ├─ social.routes.php   ⚠ loads AFTER panel.php — causes fal-ai shadow
       ├─ support.routes.php
       ├─ team.routes.php
       ├─ titan_admin.routes.php
       ├─ titan_core.routes.php
       └─ work.routes.php
BroadcastServiceProvider::boot()
  └─ channels.php
```
