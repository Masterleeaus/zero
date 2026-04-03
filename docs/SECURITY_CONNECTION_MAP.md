# Security Domain — Connection Map

**Output for Stage 5 of the Security Integration Pass**

---

## HRM Access Tiers

| Integration point | How connected | Status |
|---|---|---|
| Staff profile access | `TimesheetPolicy` gates on company_id match | ✅ existing |
| Admin-only HR operations | `is_superadmin` + `AdminPermissionMiddleware` | ✅ existing |
| Security audit on HR events | `SecurityAuditService::record(TYPE_POLICY_DENIED)` can be called from any policy | ✅ available |

---

## Finance Approvals

| Integration point | How connected | Status |
|---|---|---|
| Invoice / Quote / Payment approval | `InvoicePolicy`, `QuotePolicy`, `PaymentPolicy` | ✅ existing |
| Finance route protection | `throttle:120,1` + `auth` in `money.routes.php` | ✅ existing |
| Security audit on finance denials | `SecurityAuditService` callable from finance policies | ✅ available |

---

## Inventory Permissions

| Integration point | How connected | Status |
|---|---|---|
| Inventory route auth | `auth` + `throttle:120,1` in `inventory.routes.php` | ✅ existing |
| Inventory company scoping | `BelongsToCompany` on all Inventory models | ✅ existing |
| Security audit | `SecurityAuditService` callable on policy denial | ✅ available |

---

## Comms Transport Trust

| Integration point | How connected | Status |
|---|---|---|
| Broadcast channel auth | Laravel Echo + private channels via `auth` | ✅ existing |
| API rate limiting | `throttle:api` (60/min) on API routes | ✅ existing |
| Email blacklist | `BlackListEmailMiddleware` blocks blacklisted senders | ✅ implemented this pass |

---

## Omni Channel Auth

| Integration point | How connected | Status |
|---|---|---|
| Chat / social routes | `auth` + `throttle:120,1` | ✅ existing |
| Chatbot CSRF bypass | `ChatbotCsrf` middleware — scoped to chatbot routes only | ✅ existing |
| Login hardening for Omni registration | `CyberSecurityMiddleware` covers all auth endpoints | ✅ implemented this pass |

---

## PWA Node Identity

| Integration point | How connected | Status |
|---|---|---|
| Device trust | `NodeTrustService` — HMAC-signed device fingerprint with `company_id` | ✅ existing |
| Signal ingress verification | `SignalSignatureValidator` — validates HMAC before promoting | ✅ existing |
| Security audit on untrusted device | `SecurityAuditService::record(TYPE_DEVICE_UNTRUSTED)` — event constant ready | ✅ available |

---

## Device Sync Trust

| Integration point | How connected | Status |
|---|---|---|
| PWA device table | `tz_pwa_devices` with `trust_level`, `last_trust_check_at` | ✅ existing |
| Trust promotion | `PromotePwaIngressJob` verifies device trust before processing | ✅ existing |
| Audit on sync rejection | `SecurityAuditEvent::TYPE_SIGNAL_REJECTED` constant ready | ✅ available |

---

## Signal Ingestion Verification

| Integration point | How connected | Status |
|---|---|---|
| Signal signature validation | `ValidateZylosSignature` middleware on Zylos routes | ✅ existing |
| PWA signal ingress | `SignalSignatureValidator` + `tz_pwa_signal_ingress` conflict detection | ✅ existing |
| Security audit on rejection | `SecurityAuditEvent::TYPE_SIGNAL_REJECTED` — call `SecurityAuditService::record()` | ✅ available |

---

## Middleware Integration Points

```
web group
  └── VerifyCsrfToken                    [A] host
  └── security.blacklist_ip              [NEW] blocks before any route logic
  └── security.blacklist_email           [NEW] blocks on email presence
  └── security.cyber                     [NEW] login hardening + unique session
  └── security.login_expiry              [NEW] force logout on expired accounts

dashboard group (auth + throttle:120,1)
  └── updateUserActivity
  └── titan.tenancy                      enforces company_id
  └── security routes exposed under /dashboard/security/*
```

---

## Event Bus Connections

| Event | Fired by | Consumed by |
|---|---|---|
| `LoginLockoutEvent` | `CyberSecurityMiddleware` | (deferred) `LockoutEmailListener` |
| `SecurityAuditEvent` (DB record) | `SecurityAuditService::record()` | Admin audit trail API |

---

## Summary of New Connections Added This Pass

1. **Security middleware registered** in `Kernel.php` as named aliases ready for route-level use.
2. **`SecurityAuditService`** is injectable across all domain services and policies.
3. **Security routes** at `/dashboard/security/*` expose admin API for config and audit trail.
4. **`SecurityAuditEvent` constants** (`TYPE_*`) provide typed hooks for all domain teams.
