# Security Domain — Source Module Audit

**Output for Stage 2 of the Security Integration Pass**

Source: `CodeToUse/PWA/platform/CyberSecurity/`

---

## Module Overview

The CyberSecurity module is a Laravel modular-package (Nwidart/laravel-modules style)
that adds configurable login hardening, IP/email blacklisting, single-session enforcement,
and login-expiry management to a Worksuite-family Laravel application.

---

## Entities (Models)

| Entity | Table | Purpose |
|---|---|---|
| `CyberSecurity` | `cyber_securities` | Singleton login-protection config |
| `CyberSecuritySetting` | `cyber_security_settings` | Module licence / purchase settings |
| `BlacklistIp` | `blacklist_ips` | Blocked IP addresses |
| `BlacklistEmail` | `blacklist_emails` | Blocked emails / domains |
| `LoginExpiry` | `login_expiries` | Per-user forced-logout expiry dates |

---

## Controllers

| Controller | Routes served | Notes |
|---|---|---|
| `CyberSecuritySettingController` | `/account/cyber-security` (GET/PUT) | Tabbed settings: security, single-session, IP list, email list, login-expiry |
| `BlacklistIpController` | CRUD under `/account/cyber-security` | IP blacklist management |
| `BlacklistEmailController` | CRUD under `/account/cyber-security` | Email blacklist management |
| `LoginExpiryController` | CRUD under `/account/cyber-security` | Per-user expiry management |

---

## Middleware

| Middleware | Classification | Purpose |
|---|---|---|
| `CyberSecurityMiddleware` | Identity hardening | Login rate limiting, progressive lockout, mass-registration auto-block, unique-session |
| `BlackListIpMiddleware` | API protection | Block requests from blacklisted IPs |
| `BlackListEmailMiddleware` | Identity hardening | Block requests from blacklisted emails / domains |
| `LoginExpiryMiddleware` | Token lifecycle | Force logout on expired login |

---

## Events

| Event | Purpose |
|---|---|
| `LockoutEmailEvent` | Fired when lockout count reaches `alert_after_lockouts` threshold |

---

## Listeners

| Listener | Handles | Purpose |
|---|---|---|
| `LockoutEmailListener` | `LockoutEmailEvent` | Sends alert email to configured security admin address |
| `DifferentIpListener` | Login success event | Clears rate-limit counters; sends IP-change notification if `ip_check` is on |
| `CompanyCreatedListener` | Company created | Seeds `cyber_securities` singleton for new tenants (Worksuite-specific) |

---

## Notifications

| Notification | Channel | Purpose |
|---|---|---|
| `LockoutEmailNotification` | Mail | Alert email on repeated lockout |
| `DifferentIpNotification` | Mail | Alert email on login from new IP |

---

## Migrations

| Migration | Tables |
|---|---|
| `2023_11_11_090216` | `cyber_security_settings` |
| `2023_11_22_082732` | `cyber_securities` |
| `2023_11_23_044655` | `blacklist_ips` |
| `2023_11_23_110035` | `blacklist_emails` |
| `2023_11_23_164003` | `login_expiries` |
| `2024_01_24_093636` | Remove module entry (maintenance) |

---

## Classification

| Module Component | Classification |
|---|---|
| `CyberSecurityMiddleware` | Identity hardening + rate limiting |
| `BlackListIpMiddleware` | API protection |
| `BlackListEmailMiddleware` | Identity hardening |
| `LoginExpiryMiddleware` | Token lifecycle |
| `CyberSecurity` model | Authorization (config) |
| `BlacklistIp` model | API protection |
| `BlacklistEmail` model | Identity hardening |
| `LoginExpiry` model | Token lifecycle |
| `LockoutEmailEvent` | Audit logging |
| `LockoutEmailListener` | Anomaly detection / alerting |
| `DifferentIpListener` | Anomaly detection |
| Notifications | Activity tracking |

---

## Infrastructure Layers to Discard

The following layers from the source module are **not** imported because equivalent
host infrastructure already exists:

| Discarded | Reason |
|---|---|
| `CyberSecurityServiceProvider` | Host uses standard `AppServiceProvider` + `Kernel.php` |
| `EventServiceProvider` | Host has its own `EventServiceProvider` |
| `RouteServiceProvider` (module) | Host `RouteServiceProvider` loads all core routes |
| `CyberSecuritySetting` entity | Module licence management — not applicable to Titan Zero host |
| `CompanyCreatedListener` | Company creation logic is different in Titan Zero host |
| Views / Blade templates | Host uses its own panel theme system |
| Module `config.php` | Not applicable (no module system in host) |
| `xss_ignore.php` | XSS is handled by Laravel's built-in output escaping |
| Language files | Host uses its own i18n stack |
| `laraupdater.json` | Module updater — not applicable |
