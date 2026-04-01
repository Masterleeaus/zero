# ZERO CORE ROUTE DIFF

Generated: Prompt 1 — Route Comparison

---

## Host Route Files (Canonical)

| File | Prefix | As | Notes |
|------|--------|----|-------|
| `routes/core/crm.routes.php` | `dashboard/user/...` | `dashboard.user.*` | CRM domain |
| `routes/core/work.routes.php` | `dashboard/user/...` | `dashboard.user.*` | Work domain |
| `routes/core/money.routes.php` | `dashboard/user/...` | `dashboard.user.*` | Finance domain |
| `routes/core/signals.routes.php` | `dashboard/user/titan-signals` | `dashboard.user.titan-signals.*` | Signal pipeline UI |
| `routes/core/rewind.routes.php` | `dashboard/user/titan-rewind` | `dashboard.user.titan-rewind.*` | Rewind scaffolding |
| `routes/core/insights.routes.php` | `dashboard/user/insights` | `dashboard.user.insights.*` | Analytics |
| `routes/core/team.routes.php` | `dashboard/user/team` | `dashboard.user.team.*` | Team management |
| `routes/core/support.routes.php` | (pending migration) | — | TODO in host |
| `routes/core/social.routes.php` | `dashboard/user/business-suite` | `dashboard.user.business-suite.*` | Social/AI suite |
| `routes/panel.php` | `dashboard/` | `dashboard.*` | Main panel |
| `routes/api.php` | `api/` | `api.*` | API surface |
| `routes/web.php` | Various | — | Public web |
| `routes/auth.php` | — | `auth.*` | Auth routes |

---

## Source Route Files (from titancore)

| Source File | Status | Notes |
|-------------|--------|-------|
| `routes/core/titan_core.routes.php` | **New — not in host** | Routes for TitanCoreStatusController; migrate in Prompt 2 |
| `routes/core/signals.routes.php` | ✅ Same | Already in host |
| `routes/web.php` | ⚠️ Duplicate | Exclude — host owns this |
| `routes/api.php` | ⚠️ Duplicate | Exclude — host owns this |
| `routes/auth.php` | ⚠️ Duplicate | Exclude — host owns this |
| `routes/panel.php` | ⚠️ Duplicate | Exclude — host owns this |

---

## New Routes to Add (Prompt 2)

```
GET  /dashboard/user/business-suite/core          dashboard.user.business-suite.core.index
GET  /dashboard/user/business-suite/core/status   dashboard.user.business-suite.core.status
```

Both route to `App\Http\Controllers\TitanCore\TitanCoreStatusController`.

---

## Route Conflicts

| Conflict | Description | Resolution |
|----------|-------------|-----------|
| `routes/web.php` | Source ships full copy of host web.php | **Exclude** source version entirely |
| `routes/api.php` | Source ships full copy of host api.php | **Exclude** source version entirely |
| `routes/panel.php` | Source ships full copy of host panel.php | **Exclude** source version; merge new titan_core routes only |

---

## MCP API Routes (Prompt 4)

Future MCP tool endpoints will be added under:

```
/api/titan/mcp/{tool}
```

Route namespace: `api.titan.mcp.*`

Must be protected by Sanctum, rate-limited, and routed through `TitanAIRouter`.

---

## Named Route Stability

All existing named routes must remain stable. New Titan routes use the `dashboard.user.business-suite.core.*` prefix to avoid conflicts with existing routes.
