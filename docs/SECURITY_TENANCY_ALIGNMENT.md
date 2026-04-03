# Security Domain — Tenancy Alignment

**Output for Stage 4 of the Security Integration Pass**

---

## Tenancy Model

Titan Zero uses `company_id` as the tenant boundary.  All tenant-specific data
carries a `company_id` foreign key and the `BelongsToCompany` trait applies a
global Eloquent scope so queries are automatically filtered.

`EnforceTitanTenancy` middleware rejects any authenticated request where the
user's `company_id` is missing.

---

## Security Models — Tenancy Classification

| Model | Table | company_id? | Reasoning |
|---|---|---|---|
| `CyberSecurityConfig` | `cyber_securities` | ❌ system-wide | Login protection config applies globally to all tenants equally |
| `BlacklistIp` | `blacklist_ips` | ❌ system-wide | IP blocks must apply at the network edge before tenant context is established |
| `BlacklistEmail` | `blacklist_emails` | ❌ system-wide | Email blocks apply before tenant routing |
| `LoginExpiry` | `login_expiries` | ❌ (user-scoped via `user_id` FK) | Expiry is tied to a specific user, who already belongs to a tenant via the users table |
| `SecurityAuditEvent` | `security_audit_events` | ✅ tenant-aware | Audit trail must be tenant-isolated for privacy and compliance |

### Rationale for system-wide tables

`cyber_securities`, `blacklist_ips`, and `blacklist_emails` operate at the
authentication boundary — before a session is established and before a
`company_id` can be resolved.  Scoping them per-tenant would require resolving
the tenant before auth completes, which is not possible in the current
architecture.

---

## security_audit_events — Tenant Isolation Verification

The `security_audit_events` table:
- Has a `company_id` column indexed for fast per-tenant queries.
- The `SecurityAuditEvent` model uses the `BelongsToCompany` trait, which adds a
  global Eloquent scope filtering on `company_id`.
- `SecurityAuditService::record()` resolves `company_id` from the authenticated
  user when not explicitly supplied.
- `SecurityAuditService::recentForCompany()` and `eventsOfType()` use
  `withoutGlobalScopes()` + an explicit `where('company_id', $companyId)` to
  ensure the caller receives only their tenant's events.

---

## All Queries Scoped

| Query site | Method | Tenant-safe? |
|---|---|---|
| `SecurityAuditService::record()` | Resolves company_id from Auth user | ✅ |
| `SecurityAuditService::recentForCompany()` | Explicit company_id filter | ✅ |
| `SecurityAuditService::eventsOfType()` | Explicit company_id filter | ✅ |
| `BlacklistIp` reads | No tenant scope (system-wide by design) | ✅ intentional |
| `BlacklistEmail` reads | No tenant scope (system-wide by design) | ✅ intentional |
| `CyberSecurityConfig::singleton()` | Single-row, no tenant | ✅ intentional |
| `LoginExpiry` reads | Filtered by `user_id` | ✅ (user belongs to tenant) |

---

## All Logs Tenant-Aware

Every call to `SecurityAuditService::record()` passes the authenticated user's
`company_id`.  If no authenticated user is present (pre-auth middleware hits),
`company_id` is recorded as `null` and the event is still persisted for
system-level review.

---

## All Tokens Tenant-Safe

- Sanctum tokens are issued per-user; users belong to a company.
- PWA node trust tokens (`NodeTrustService`) include `company_id` in the signed
  fingerprint payload, so device tokens are tenant-bound.
- No cross-tenant token sharing is possible through the security domain.

---

## Audit Trails Tenant-Separated

- `security_audit_events` is physically partitioned by `company_id` (indexed).
- `SecuritySettingsController::auditTrail()` enforces that the requesting user's
  `company_id` is used as the filter — no cross-tenant data exposure.
- The existing `tz_audit_log` (AI/signal audit) is not yet scoped by tenant;
  that is tracked as a separate concern outside this pass.

---

## Open Items

| Item | Status |
|---|---|
| `tz_audit_log` lacks `company_id` | Deferred — out of scope for this pass; tracked as tech debt |
| Per-tenant blacklist support | Deferred — current architecture intentionally uses system-wide lists |
| Tenant-specific login retry config | Deferred — single config serves all tenants currently |
