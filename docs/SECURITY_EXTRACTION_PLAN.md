# Security Domain — Extraction Plan

**Output for Stage 6 of the Security Integration Pass**

---

## Integration Order (Priority)

### Phase 1 — Audit Trail Layer ✅ DELIVERED

**Rationale**: Foundational. Every subsequent security feature writes to the audit trail.

- [x] `SecurityAuditEvent` model (`app/Models/Security/SecurityAuditEvent.php`)
- [x] `security_audit_events` table (migration 800100) — tenant-scoped, company_id indexed
- [x] `SecurityAuditService` — typed record method, per-company query helpers
- [x] `SecurityAuditEvent::TYPE_*` constants for all event categories

---

### Phase 2 — Permission Enrichment ✅ DELIVERED (foundation)

**Rationale**: Login protection and blacklisting prevent unauthorised access.

- [x] `CyberSecurityConfig` model + `cyber_securities` table (migration 800100)
- [x] `CyberSecurityConfigService` — singleton accessor, blacklist helpers
- [x] `BlacklistIp` model + `blacklist_ips` table
- [x] `BlacklistEmail` model + `blacklist_emails` table

---

### Phase 3 — Policy Gates ✅ DELIVERED (infrastructure)

**Rationale**: Security middleware functions as policy gates on auth routes.

- [x] `BlackListIpMiddleware` — IP gate registered as `security.blacklist_ip`
- [x] `BlackListEmailMiddleware` — Email gate registered as `security.blacklist_email`
- [x] `CyberSecurityMiddleware` — Login hardening gate registered as `security.cyber`
- [x] All 4 middleware aliases registered in `Kernel.php`

---

### Phase 4 — API Token Lifecycle ✅ DELIVERED (model + routes)

**Rationale**: Token expiry enforcement prevents stale session reuse.

- [x] `LoginExpiry` model + `login_expiries` table (migration 800100)
- [x] `LoginExpiryMiddleware` — registered as `security.login_expiry`
- [x] `SecuritySettingsController` — admin API to manage expiries

---

### Phase 5 — Device Trust Model (foundation in place)

**Rationale**: PWA node trust is handled by the existing `NodeTrustService` and
`tz_pwa_devices`. The Security domain provides audit hooks.

- [x] `SecurityAuditEvent::TYPE_DEVICE_UNTRUSTED` constant defined
- [ ] Wire `NodeTrustService` to call `SecurityAuditService::record()` on trust failures — **Phase 2 task**

---

### Phase 6 — Signal Verification Hooks (foundation in place)

**Rationale**: Signal ingress is handled by `SignalSignatureValidator`. Audit hooks are ready.

- [x] `SecurityAuditEvent::TYPE_SIGNAL_REJECTED` constant defined
- [ ] Wire `SignalSignatureValidator` to call `SecurityAuditService::record()` on rejection — **Phase 2 task**

---

### Phase 7 — Encryption Utilities (existing stack sufficient)

**Rationale**: Laravel `Crypt` + `phpseclib` 3.0.50 cover current needs.

- [x] `phpseclib` pinned to 3.0.50+ (resolved GHSA-94g3-g5v7-q4jg)
- [ ] Custom encryption helpers — **Defer**: evaluate need before adding

---

### Phase 8 — Anomaly Detection (deferred)

**Rationale**: Requires sufficient audit event volume for pattern analysis.

- [x] Event constants and audit table ready to support detection logic
- [ ] `AnomalyDetectionService` — **Future task**: analyse `security_audit_events` for patterns
- [ ] Alert notifications (`LockoutEmailListener`, `DifferentIpListener`) — **Phase 2 task**

---

## Extraction Rules Applied

| Rule | Applied |
|---|---|
| Reuse > extend > refactor > replace | Extended existing `AuditTrail` pattern; created new service for security domain |
| No duplicate auth system | Used `Laravel\Sanctum` + existing `Authenticate` middleware |
| No duplicate user model | Referenced `App\Models\User` |
| No duplicate roles tables | Used existing `spatie/laravel-permission` + `is_superadmin` column |
| No duplicate session handlers | Used Laravel built-in session; added unique-session enforcement on top |
| No duplicate tenancy logic | Used `BelongsToCompany` trait on `SecurityAuditEvent` |
| Strip module infrastructure | Discarded `CyberSecurityServiceProvider`, `EventServiceProvider`, module `RouteServiceProvider`, views, language files |
| Feature logic retained | All middleware, models, config service, audit service |

---

## Files Delivered This Pass

### New PHP files

| File | Purpose |
|---|---|
| `app/Models/Security/CyberSecurityConfig.php` | Login protection settings singleton |
| `app/Models/Security/BlacklistIp.php` | IP blacklist model |
| `app/Models/Security/BlacklistEmail.php` | Email blacklist model |
| `app/Models/Security/LoginExpiry.php` | Per-user login expiry model |
| `app/Models/Security/SecurityAuditEvent.php` | Tenant-aware security audit model |
| `app/Services/Security/SecurityAuditService.php` | Audit recording + querying |
| `app/Services/Security/CyberSecurityConfigService.php` | Config + blacklist management |
| `app/Http/Middleware/Security/BlackListIpMiddleware.php` | IP gate |
| `app/Http/Middleware/Security/BlackListEmailMiddleware.php` | Email gate |
| `app/Http/Middleware/Security/LoginExpiryMiddleware.php` | Expiry enforcement |
| `app/Http/Middleware/Security/CyberSecurityMiddleware.php` | Login hardening |
| `app/Events/Security/LoginLockoutEvent.php` | Lockout event |
| `app/Http/Controllers/Core/Security/SecuritySettingsController.php` | Admin API |
| `app/Http/Controllers/Core/Security/BlacklistIpController.php` | IP CRUD |
| `app/Http/Controllers/Core/Security/BlacklistEmailController.php` | Email CRUD |
| `routes/core/security.routes.php` | Security domain routes |
| `tests/Feature/Security/SecurityDomainTest.php` | Feature tests |

### Modified files

| File | Change |
|---|---|
| `app/Http/Kernel.php` | Added 4 middleware aliases (`security.*`) |

### New migration

| File | Tables created |
|---|---|
| `database/migrations/2026_04_03_800100_create_security_domain_tables.php` | `cyber_securities`, `blacklist_ips`, `blacklist_emails`, `login_expiries`, `security_audit_events` |
