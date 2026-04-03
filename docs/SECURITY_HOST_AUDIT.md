# Security Domain — Host Security Inventory

**Output for Stage 1 of the Security Integration Pass**

---

## Auth Guards

| Guard | Driver | Provider |
|---|---|---|
| `web` | session | `users` Eloquent model |
| `api` | token / Sanctum | `users` Eloquent model |

Auth configuration: `config/auth.php`.

---

## Middleware Stack

### Global (every request)
| Middleware | Purpose |
|---|---|
| `TrustProxies` | X-Forwarded headers |
| `RefererMiddleware` | Referer validation |
| `HandleCors` | CORS headers |
| `TrimStrings` | Input sanitisation |
| `ConvertEmptyStringsToNull` | Input sanitisation |

### Web group
| Middleware | Purpose |
|---|---|
| `EncryptCookies` | Cookie encryption |
| `VerifyCsrfToken` | CSRF protection |
| `LocaleMiddleware` | Locale detection |
| `ThemeMiddleware` | Theme detection |

### Authenticated dashboard routes (`auth` + `throttle:120,1`)
| Middleware alias | Class | Purpose |
|---|---|---|
| `auth` | `Authenticate` | Session auth gate |
| `throttle:120,1` | Laravel built-in | Rate limiting |
| `updateUserActivity` | `UpdateUserActivity` | Last-seen tracking |
| `titan.tenancy` | `EnforceTitanTenancy` | company_id enforcement |
| `titan.zylos.signature` | `ValidateZylosSignature` | Signal signature validation |
| `admin` | `AdminPermissionMiddleware` | Admin-only routes |
| `sentry.context` | `SentryContextMiddleware` | Error context enrichment |

---

## Policies

| Policy | Model | Guards |
|---|---|---|
| `ModelPolicy` | Generic | Admin / owner checks |
| `QuotePolicy` | Quote | View, create, update, delete |
| `InvoicePolicy` | Invoice | View, create, update, delete |
| `PaymentPolicy` | Payment | View, create, update, delete |
| `ExpensePolicy` | Expense | View, create |
| `UserSupportPolicy` | UserSupport | Owner / admin |
| `TimesheetPolicy` | TimesheetSubmission | Submit, approve, reject |

---

## Roles & Permissions

- Permissions managed via `spatie/laravel-permission` (configured in `app/SpatiePermissionConfig.php`).
- `App\Enums\Permissions` defines canonical permission strings: `marketplace`, `user_management`, `finance`, `settings`, `site_health`, etc.
- Role checks use `is_superadmin` boolean column on `users` table and `isAdmin()` model method.

---

## Token Handling

- API tokens: Laravel Sanctum (`laravel/sanctum`).
- OAuth / Social: `laravel/passport` 12.x (JWT 6.x internally due to upstream constraint — tracked in `docs/security-audit.md`).
- PWA node trust: `App\Services\TitanZeroPwaSystem\NodeTrustService` with HMAC-signed device fingerprints.

---

## CSRF Logic

- All web routes protected via `VerifyCsrfToken` middleware.
- Chatbot-specific CSRF bypass: `ChatbotCsrf` + `ChatbotPreflightMiddleware`.
- API routes exempt (use Sanctum token auth).

---

## Session Logic

- Driver: configurable (`SESSION_DRIVER` env).
- Session table: `sessions` (when driver = `database`).
- Session auth handled by Laravel built-ins.
- **Unique-session enforcement**: Not yet active in host (pending CyberSecurity integration — addressed by this pass).

---

## Encryption Helpers

- `phpseclib/phpseclib` 3.0.50+ — pinned to resolve AES-CBC padding oracle (GHSA-94g3-g5v7-q4jg).
- Laravel `Crypt` facade — AES-256-CBC.
- No custom encryption helpers in host (standard Laravel stack).

---

## Audit Tables (Existing)

| Table | Owner | Purpose |
|---|---|---|
| `tz_audit_log` | `App\Titan\Signals\AuditTrail` | AI/signal process audit (no tenant scoping) |
| `tz_processes` | TitanCore | Process lifecycle |
| `tz_process_states` | TitanCore | State transitions |
| `tz_pwa_signal_ingress` | PWA System | Signal ingress tracking |

**Gap**: No tenant-aware general security audit table existed before this pass.

---

## Tenant Boundary Enforcement

- `BelongsToCompany` trait: global scope on `company_id`, auto-fill on create.
- `EnforceTitanTenancy` middleware: rejects requests where `company_id` is missing.
- PWA device isolation: `company_id` on `tz_pwa_devices`.

---

## Rate Limiting

- Global API: 60 req/min per user/IP (`api` limiter in `RouteServiceProvider`).
- Dashboard routes: `throttle:120,1` (120 req/min).
- Login-specific rate limiting: NOT present in host before this pass (added by CyberSecurity integration).

---

## Broadcast Auth

- Laravel Echo + Pusher-compatible broadcast via `config/broadcasting.php`.
- Private channels guarded by `auth` middleware.

---

## Queue Auth

- Three isolated queues: `titan-ai`, `titan-signals`, `titan-skills`.
- Queue workers require app-level authentication; no per-queue auth tokens.
