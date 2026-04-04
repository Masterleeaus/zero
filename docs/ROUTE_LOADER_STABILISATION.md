# ROUTE_LOADER_STABILISATION.md

**Phase 9 — Step 4: Route Loader Stabilisation**
**Date:** 2026-04-04
**Pass:** Phase 9 — Critical Structural Stabilisation

---

## Current State

Route loading is handled entirely by `app/Providers/RouteServiceProvider.php`.

### Architecture

```
RouteServiceProvider::boot()
├── API group: routes/api.php
└── Web group:
    ├── routes/web.php
    └── Auth + throttle:120,1 group:
        └── loadCoreRoutes() → glob('routes/core/*.routes.php')
```

### `loadCoreRoutes()` Implementation

```php
protected function loadCoreRoutes(): void
{
    $corePath = base_path('routes/core');

    if (! is_dir($corePath)) {
        return;
    }

    $files = glob($corePath . '/*.routes.php') ?: [];

    // Enforce simple lowercase naming (e.g. crm.routes.php, titan_core.routes.php).
    $files = array_values(array_filter($files, static function ($file) {
        return (bool) preg_match('/^[a-z][a-z_]*\.routes\.php$/', basename($file));
    }));

    sort($files);

    foreach ($files as $routeFile) {
        require $routeFile;
    }
}
```

---

## Boundary Assessment

### Is CodeToUse at risk of being loaded as routes?

**No.** The glob pattern is `routes/core/*.routes.php` — only files inside `routes/core/` are included. `CodeToUse/` is a top-level directory entirely separate from `routes/`.

The `CodeToUse/` path contains many route files (e.g. `CodeToUse/Voice/*/routes/api.php`, `CodeToUse/Signals/titan_signal/TitanSignalBase/routes/titan_signals.php`) but none of these are in `routes/core/` and none match the glob.

**Verdict: CodeToUse routes cannot be accidentally loaded by the current route architecture.**

---

## Confirmed Safe Route Files in `routes/core/`

| File | Status |
|------|--------|
| `admin.routes.php` | Active — admin module |
| `crm.routes.php` | Active — CRM domain |
| `docs.routes.php` | Active — DocsExecutionBridge |
| `insights.routes.php` | Active — analytics |
| `mcp.routes.php` | Active — MCP module |
| `mesh.routes.php` | Active — TitanMesh |
| `money.routes.php` | Active — Finance/Money |
| `predict.routes.php` | Active — TitanPredict |
| `pwa.routes.php` | Active — PWA layer |
| `repair.routes.php` | Active — Repair domain |
| `rewind.routes.php` | Active — TitanRewind |
| `route.routes.php` | Active — Route/dispatch |
| `signals.routes.php` | Active — TitanSignals |
| `social.routes.php` | Active — Social media |
| `support.routes.php` | Active — Support domain |
| `team.routes.php` | Active — Team/HR |
| `timegraph.routes.php` | Active — ExecutionTimeGraph |
| `titan_admin.routes.php` | Active — admin panel routes |
| `titan_core.routes.php` | Active — core AI routes |
| `work.routes.php` | Active — Work/Jobs domain |

---

## Known Risks (Low)

| Risk | Description | Status |
|------|-------------|--------|
| Auto-include via glob | Any new `.routes.php` file dropped in `routes/core/` is auto-loaded. Future integration passes must be aware that adding a file here immediately activates it. | Acceptable — by design |
| No explicit opt-in list | New route files from future integrations may be loaded without review. | Mitigated by regex naming filter |
| Double-loader check | `routes/web.php` and `routes/panel.php` must not explicitly include any `routes/core/` files. | Verified — neither file references `routes/core/`. |

---

## Recommendation

The route loader boundary is currently safe. No code changes were required.

For future integration passes:
- Route files should only be placed in `routes/core/` after explicit architectural review
- The naming filter `[a-z][a-z_]*.routes.php` provides some protection but is not an explicit allowlist
- Consider adding an explicit `ROUTE_EXCLUSIONS` array to `RouteServiceProvider` as a future hardening option
