# Security Domain — Overlap Matrix

**Output for Stage 3 of the Security Integration Pass**

Legend:
- **A** — Already exists in host (identical or equivalent)
- **B** — Enrich host (extend / add capability to existing)
- **C** — Extract logic only (don't import the file; port the logic)
- **D** — Missing — import candidate (create in host)
- **E** — Defer (not needed for this pass)

---

## Database Tables

| Table | Source module | Host | Decision |
|---|---|---|---|
| `cyber_securities` | ✅ exists | ❌ missing | **D** — Created in migration 800100 |
| `blacklist_ips` | ✅ exists | ❌ missing | **D** — Created in migration 800100 |
| `blacklist_emails` | ✅ exists | ❌ missing | **D** — Created in migration 800100 |
| `login_expiries` | ✅ exists | ❌ missing | **D** — Created in migration 800100 |
| `cyber_security_settings` | ✅ exists (module licence) | ❌ missing | **E** — Defer: module licence management not required |
| `security_audit_events` | ❌ missing | ❌ missing | **D** — New tenant-aware security audit table |

---

## Models

| Entity | Source | Host | Decision |
|---|---|---|---|
| `CyberSecurity` | `Modules\CyberSecurity\Entities\CyberSecurity` | — | **C** → `App\Models\Security\CyberSecurityConfig` |
| `BlacklistIp` | `Modules\CyberSecurity\Entities\BlacklistIp` | — | **C** → `App\Models\Security\BlacklistIp` |
| `BlacklistEmail` | `Modules\CyberSecurity\Entities\BlacklistEmail` | — | **C** → `App\Models\Security\BlacklistEmail` |
| `LoginExpiry` | `Modules\CyberSecurity\Entities\LoginExpiry` | — | **C** → `App\Models\Security\LoginExpiry` |
| `CyberSecuritySetting` | `Modules\CyberSecurity\Entities\CyberSecuritySetting` | — | **E** — Defer |
| `SecurityAuditEvent` | ❌ missing | — | **D** — New: tenant-aware audit log model |

---

## Middleware

| Middleware | Source | Host equivalent | Decision |
|---|---|---|---|
| `CyberSecurityMiddleware` | ✅ source | ❌ none | **C** → `App\Http\Middleware\Security\CyberSecurityMiddleware` (adapted) |
| `BlackListIpMiddleware` | ✅ source | ❌ none | **C** → `App\Http\Middleware\Security\BlackListIpMiddleware` |
| `BlackListEmailMiddleware` | ✅ source | ❌ none | **C** → `App\Http\Middleware\Security\BlackListEmailMiddleware` |
| `LoginExpiryMiddleware` | ✅ source | ❌ none | **C** → `App\Http\Middleware\Security\LoginExpiryMiddleware` |
| CSRF protection | N/A | **A** `VerifyCsrfToken` | Already exists |
| Rate limiting | N/A | **A** `throttle:120,1` (RouteServiceProvider) | Already exists — augmented by `CyberSecurityMiddleware` |
| Auth | N/A | **A** `Authenticate` | Already exists |
| Tenancy | N/A | **A** `EnforceTitanTenancy` | Already exists |

---

## Controllers

| Controller | Source | Host | Decision |
|---|---|---|---|
| `CyberSecuritySettingController` | ✅ source | ❌ none | **C** → `App\Http\Controllers\Core\Security\SecuritySettingsController` |
| `BlacklistIpController` | ✅ source | ❌ none | **C** → `App\Http\Controllers\Core\Security\BlacklistIpController` |
| `BlacklistEmailController` | ✅ source | ❌ none | **C** → `App\Http\Controllers\Core\Security\BlacklistEmailController` |
| `LoginExpiryController` | ✅ source | ❌ none | **E** — Defer (login expiry managed via settings for now) |

---

## Events / Listeners

| Component | Source | Host | Decision |
|---|---|---|---|
| `LockoutEmailEvent` | ✅ source | ❌ none | **C** → `App\Events\Security\LoginLockoutEvent` |
| `LockoutEmailListener` | ✅ source | ❌ none | **E** — Defer: alerting notifications are a Phase 2 item |
| `DifferentIpListener` | ✅ source | ❌ none | **E** — Defer: IP-change notifications are a Phase 2 item |
| `CompanyCreatedListener` | ✅ source | ❌ not applicable | **E** — Company seeding logic is host-specific |

---

## Services

| Component | Source | Host | Decision |
|---|---|---|---|
| Security audit service | ❌ missing in source | `AuditTrail` (AI-only, no tenant) | **D** — New `SecurityAuditService` with tenant scoping |
| Config service | ❌ missing in source | — | **D** — New `CyberSecurityConfigService` |

---

## Routes

| Source routes | Host | Decision |
|---|---|---|
| `/account/cyber-security` (web.php) | — | **C** → `routes/core/security.routes.php` under `/dashboard/security/` |

---

## Infrastructure (discard)

| Component | Decision |
|---|---|
| `CyberSecurityServiceProvider` | **A** — Host uses `Kernel.php` middleware registration |
| `EventServiceProvider` (module) | **A** — Host `EventServiceProvider` handles event bindings |
| `RouteServiceProvider` (module) | **A** — Host `RouteServiceProvider` loads all core routes |
| Views / Blade templates | **E** — Host UI is a separate concern |
| Language files | **E** — Host i18n system |
| `xss_ignore.php` | **A** — Laravel output escaping is sufficient |
| `laraupdater.json` | **E** — Not applicable |
