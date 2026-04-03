# docs/ROUTE_RENAME_RISK_MAP.md

**Phase 7 — Route Rename Risk Map**
**Date:** 2026-04-03
**Scope:** All named routes. Classifies each as safe to rename, unsafe to rename, or blocked
by hard dependencies in Blade views, controllers, redirects, Livewire, or JS.

---

## Summary

| Category | Count |
|----------|-------|
| Routes blocked from rename (high dependency) | ~85 |
| Routes safe to rename (trivial / isolated) | ~40 |
| Routes with medium rename risk | ~30 |
| Routes with no dependencies (dead or API-only) | ~15 |

---

## Methodology

Route names were cross-checked against:
- `resources/views/**/*.blade.php` — `route()` helper calls
- `app/Http/Controllers/**/*.php` — `redirect()->route()`, `to_route()`, `route()`
- `app/Livewire/**/*.php` — `route()` and `redirect()->route()`
- `routes/**/*.php` — internal route cross-references

---

## 1. BLOCKED — Unsafe to Rename Without Full Migration

These route names are referenced in multiple places (Blade + Controllers + Livewire).
Renaming requires simultaneous updates across views, controllers, and tests.

### 1a. Core Dashboard Names (panel.php — heavy Blade + controller usage)

| Route Name | Used In | Risk |
|-----------|---------|------|
| `dashboard.index` | Blade (many), Controllers (AuthenticatedSessionController, etc.) | 🔴 BLOCKED — login redirect target |
| `dashboard.admin.index` | Blade (admin nav), AdminController | 🔴 BLOCKED |
| `dashboard.admin.finance.plan.index` | Blade (many), Controllers, Livewire (AssignViewCredits) | 🔴 BLOCKED |
| `dashboard.admin.finance.plan.create` | Blade (admin), Controllers | 🔴 BLOCKED |
| `dashboard.admin.settings.general` | Blade, Controllers (redirect target) | 🔴 BLOCKED |
| `dashboard.admin.chatbot.index` | Controllers (redirect target), Blade | 🔴 BLOCKED |
| `dashboard.admin.openai.chat.list` | Controllers, Blade | 🔴 BLOCKED |
| `dashboard.admin.users.*` | Controllers, Blade (admin user management) | 🔴 BLOCKED |
| `dashboard.admin.marketplace.index` | Controllers, Blade | 🔴 BLOCKED |
| `dashboard.admin.announcements.*` | Blade, Controllers | 🔴 BLOCKED |
| `dashboard.admin.config.*` | Blade (admin config pages) | 🔴 BLOCKED |
| `dashboard.user.openai.*` | Blade (user dashboard), many views | 🔴 BLOCKED |
| `dashboard.user.payment.*` | Blade (payment flow) | 🔴 BLOCKED |
| `dashboard.user.orders.*` | Blade (order history views) | 🔴 BLOCKED |
| `login` | Framework (Authenticate middleware redirects here) | 🔴 BLOCKED — framework dependency |
| `register` | Framework/Blade | 🔴 BLOCKED |
| `forgot_password` | Blade | 🔴 BLOCKED |

### 1b. Admin Titan Core Names (titan_admin.routes.php)

These names use non-standard prefix `admin.titan.core.*` (missing `dashboard.` prefix).
They ARE referenced in Blade views and Controllers:

| Route Name | Blade/Controller Usage | Rename Risk |
|-----------|----------------------|------------|
| `admin.titan.core.models` | Blade (titan admin views), Controllers (redirects) | 🔴 BLOCKED — used in redirects |
| `admin.titan.core.memory` | Blade, Controllers | 🔴 BLOCKED |
| `admin.titan.core.queues` | Blade, Controllers | 🔴 BLOCKED |
| `admin.titan.core.budgets` | Blade, Controllers | 🔴 BLOCKED |
| `admin.titan.core.signals` | Blade | 🟠 MEDIUM — update views |
| `admin.titan.core.skills.*` | Blade | 🟠 MEDIUM |
| `admin.titan.core.health.*` | Blade | 🟠 MEDIUM |

**Note:** Normalising these to `dashboard.admin.titan.core.*` requires updating every
Blade view in `resources/views/default/panel/admin/titancore/` and every controller
redirect. Do NOT rename until a dedicated blade-update pass is planned.

### 1c. CRM Routes (crm.routes.php)

| Route Name | Dependencies |
|-----------|-------------|
| `dashboard.crm.customers.show` | Controllers (redirect), Blade (customer views) | 🔴 BLOCKED |
| `dashboard.crm.customers.*` | Blade (CRM nav, customer forms) | 🔴 BLOCKED |
| `dashboard.crm.enquiries.*` | Blade (enquiry forms) | 🔴 BLOCKED |

### 1d. Money Routes (money.routes.php)

| Route Name | Dependencies |
|-----------|-------------|
| `dashboard.money.invoices.show` | Controllers (redirect after create), Blade | 🔴 BLOCKED |
| `dashboard.money.quotes.*` | Blade (quote builder), Controllers | 🔴 BLOCKED |
| `dashboard.money.expenses.*` | Blade, Controllers | 🟠 MEDIUM |

### 1e. Inventory Routes (inventory.routes.php)

| Route Name | Dependencies |
|-----------|-------------|
| `dashboard.inventory.items.*` | Controllers (redirect), Blade | 🔴 BLOCKED |
| `dashboard.inventory.suppliers.*` | Controllers (redirect), Blade | 🔴 BLOCKED |
| `dashboard.inventory.warehouses.*` | Controllers (redirect), Blade | 🔴 BLOCKED |
| `dashboard.inventory.purchase-orders.*` | Controllers (redirect), Blade | 🔴 BLOCKED |
| `dashboard.inventory.stocktakes.*` | Controllers (redirect), Blade | 🔴 BLOCKED |

### 1f. Team / Zone Routes (team.routes.php)

| Route Name | Dependencies |
|-----------|-------------|
| `dashboard.team.service-area-regions.*` | Controllers (redirect), Blade | 🔴 BLOCKED |
| `dashboard.team.service-area-districts.*` | Controllers (redirect), Blade | 🔴 BLOCKED |
| `dashboard.team.service-area-branches.*` | Controllers (redirect), Blade | 🔴 BLOCKED |
| `dashboard.team.zones.*` | Controllers, Blade | 🟠 MEDIUM |

### 1g. Webhook Routes

| Route Name | Dependencies | Risk |
|-----------|-------------|------|
| `webhooks.stripe.success` | PaymentProcessController return redirects, Stripe dashboard config | 🔴 BLOCKED — external registration |
| `webhooks.stripe.cancel` | Same | 🔴 BLOCKED |

**Special note on webhook names:** Even though `webhooks.stripe.success` has a dangerous
name collision (CRIT-01), the URI itself is what Stripe calls back to. The generated URL
from `route('webhooks.stripe.success', [...])` may be registered in Stripe's dashboard.
Any rename must be coordinated with external payment gateway configuration.

---

## 2. SAFE TO RENAME — Low Dependency, Isolated

These routes are either new, API-only, or have no confirmed Blade/controller references.

| Route Name | Current File | Safe Rename Target | Notes |
|-----------|-------------|-------------------|-------|
| `tiktok.verify` | social.routes.php | `social-media.tiktok.verify` | Only social extension views use this; extension is missing anyway |
| `demo-data` | social.routes.php | `social-media.demo-data` | Extension-only, not in core views |
| `pwa.manifest` | pwa.routes.php | `pwa.manifest` | Already good name; no rename needed |
| `pwa.handshake` | pwa.routes.php | `pwa.handshake` | API-only |
| `titan.mcp.capabilities` | mcp.routes.php | `titan.mcp.capabilities` | API-only |
| `titan.mcp.invoke` | mcp.routes.php | `titan.mcp.invoke` | API-only |
| `titan.signal.callback` | mcp.routes.php | `titan.signal.callback` | API-only (Zylos) |
| `portal.service.index` | portal.routes.php | `dashboard.portal.service.index` | Portal views only; normalise prefix |
| `work.projects.index` | project.routes.php | `dashboard.work.projects.index` | New module; few references |
| `repair.orders.index` | repair.routes.php | `dashboard.repair.orders.index` | Blade references exist (see below) |

**⚠️ Repair routes exception:** Although marked "safe", repair route names ARE used
in Blade views (`repair.orders.index`, `repair.orders.show`, etc.). Renaming requires
updating all repair Blade views simultaneously.

---

## 3. MEDIUM RISK — Renameable with Controlled Migration

These names are inconsistent with conventions but have a modest number of references.

| Route Name | Issue | Migration Effort |
|-----------|-------|----------------|
| `titanrewind.cases.index` | Should be `dashboard.user.titanrewind.cases.index` | ~15 Blade view files to update |
| `repair.orders.*` | Should be `dashboard.repair.orders.*` | ~12 Blade view files to update |
| `repair.templates.*` | Should be `dashboard.repair.templates.*` | ~6 Blade view files |
| `admin.titan.core.*` | Should be `dashboard.admin.titan.core.*` | ~20 Blade view files + controller redirects |
| `portal.service.*` | Should be `dashboard.portal.service.*` | ~5 portal Blade files |
| `work.projects.*` | Should be `dashboard.work.projects.*` | ~3 Blade files (new module) |

---

## 4. RENAME DEPENDENCY MATRIX

Routes that, if renamed, would break other currently working systems:

| If This Route Is Renamed | It Breaks |
|--------------------------|-----------|
| `dashboard.index` | Auth middleware redirect, post-login redirect in Authenticate.php |
| `login` | Framework-level redirect from `Authenticate` middleware |
| `register` | Registration flow, Blade nav links |
| `dashboard.admin.settings.general` | Admin controller redirects after settings save |
| `dashboard.admin.finance.plan.index` | 3 controllers + Livewire AssignViewCredits |
| `admin.titan.core.models` | TitanCoreAdminController redirects |
| `admin.titan.core.memory` | TitanCoreAdminController redirects |
| `admin.titan.core.queues` | TitanCoreAdminController redirects |
| `webhooks.stripe.success` | PaymentProcessController, external Stripe dashboard |
| `dashboard.crm.customers.show` | CRM controller redirects |
| `dashboard.money.invoices.show` | InvoiceController post-create redirect |
| `dashboard.inventory.items.index` | InventoryItemController redirects |

---

## 5. NAMING CONVENTION VIOLATIONS (Not Blocked, But Should Be Fixed)

These route names violate the `dashboard.<domain>.*` or `dashboard.user/admin.*` convention
but are not otherwise blocked. They should be normalised in a dedicated naming pass.

| Current Name Pattern | Expected Pattern | Files Affected |
|---------------------|-----------------|----------------|
| `repair.*` | `dashboard.repair.*` | repair.routes.php + ~12 blade views |
| `titanrewind.*` | `dashboard.user.titanrewind.*` | rewind.routes.php + ~15 blade views |
| `portal.service.*` | `dashboard.portal.service.*` | portal.routes.php + ~5 blade views |
| `work.projects.*` | `dashboard.work.projects.*` | project.routes.php + ~3 blade views |
| `admin.titan.core.*` | `dashboard.admin.titan.core.*` | titan_admin.routes.php + ~20 blade views |
| `titan.mcp.*` | Stay as `titan.mcp.*` (API, not dashboard) | No change needed |
| `pwa.*` | Stay as `pwa.*` (API, not dashboard) | No change needed |

---

## 6. Routes with No Known Dependencies (Low-Risk Cleanup)

| Route Name | File | Notes |
|-----------|------|-------|
| `dashboard.admin.marketplace.cart` (line 446) | panel.php | Duplicate registration — second instance can be removed |
| `verify-otp` GET | auth.php | GET and POST share same name; GET has no unique name |
| `webhook.` (singular prefix) | webhooks.php | Duplicate of `webhooks.` (plural) |

---

## 7. Summary Table

| Category | Example Routes | Count |
|----------|---------------|-------|
| 🔴 Blocked — never rename without full migration | `dashboard.index`, `login`, `webhooks.stripe.success`, `dashboard.admin.finance.*` | ~85 |
| 🟠 Medium risk — rename with controlled view update | `admin.titan.core.*`, `repair.*`, `titanrewind.*` | ~30 |
| 🟢 Safe — isolated or new routes | `pwa.*`, `titan.mcp.*`, `portal.service.*`, `work.projects.*` | ~40 |
| 🗑️ Cleanup only — duplicate/dead registrations | `webhooks.stripe.cancel` (line 15 instance), marketplace.cart (line 446) | ~5 |
