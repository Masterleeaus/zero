# TITAN CORE SECURITY MODEL

**Version:** Prompt 6  
**Status:** Phase 6.11 Hardened

---

## Security Layers

### 1. Authentication

| Surface | Mechanism |
|---------|-----------|
| MCP endpoints (`/api/titan/mcp/*`) | `auth:sanctum` — Bearer token required |
| Admin panel routes | `auth` + `AdminPermissionMiddleware` |
| Skill callbacks (`/api/titan/signal/callback`) | `ValidateZylosSignature` HMAC-SHA256 |

No anonymous access is permitted on any Titan endpoint.

---

### 2. Tenancy Enforcement

| Middleware | Class | Enforces |
|-----------|-------|---------|
| `EnforceTitanTenancy` | `App\Http\Middleware\TitanCore\EnforceTitanTenancy` | `company_id` present on authenticated user |

- `company_id` is always resolved server-side from the authenticated user.
- Never accepted from request body for tenancy scoping.
- Applied to all `/api/titan/mcp/*` routes.

---

### 3. Signature Validation

| Middleware | Class | Mechanism |
|-----------|-------|---------|
| `ValidateZylosSignature` | `App\Http\Middleware\TitanCore\ValidateZylosSignature` | `HMAC-SHA256(body, ZYLOS_SECRET)` compared to `X-Zylos-Signature` header using `hash_equals()` |

- Timing-safe comparison via `hash_equals()`.
- Unsigned callbacks are rejected with HTTP 401.
- Empty `ZYLOS_SECRET` also rejects with HTTP 401.

---

### 4. Rate Limiting

| Endpoint | Limit |
|---------|-------|
| `/api/titan/mcp/capabilities` | `throttle:60,1` |
| `/api/titan/mcp/invoke` | `throttle:60,1` |
| `/api/titan/signal/callback` | `throttle:120,1` |
| Dashboard routes | `throttle:120,1` |

---

### 5. Permission Gates

Memory write, signal dispatch, and skill execution capabilities check:
- `auth:sanctum` — valid token
- `EnforceTitanTenancy` — `company_id` present
- `approval_aware` capabilities — approval chain respected before execution

---

### 6. Token Budget (Abuse Prevention)

`TitanTokenBudget` enforces:
- Per-request token cap (`TITAN_AI_PER_REQUEST_LIMIT`)
- Per-user daily cap (`TITAN_AI_PER_USER_DAILY_LIMIT`)
- Per-company daily cap (`TITAN_AI_PER_COMPANY_DAILY_LIMIT`)
- Global daily cap (`TITAN_AI_DAILY_LIMIT`)

Exceeded budgets:
1. Block execution (HTTP 200 with `status: budget_exceeded`)
2. Emit `TitanCoreActivity` event with `status: blocked`
3. Log in audit trail

---

### 7. Protected Routes Summary

| Route | Middleware Stack |
|-------|----------------|
| `GET /api/titan/mcp/capabilities` | auth:sanctum, EnforceTitanTenancy, throttle:60,1 |
| `POST /api/titan/mcp/invoke` | auth:sanctum, EnforceTitanTenancy, throttle:60,1 |
| `POST /api/titan/signal/callback` | ValidateZylosSignature, throttle:120,1 |
| `GET /dashboard/user/business-suite/core/*` | auth, updateUserActivity, throttle:120,1 |
| `GET /dashboard/user/titan-signals/*` | auth, updateUserActivity, throttle:120,1 |
| `GET /dashboard/user/titanrewind/*` | auth, updateUserActivity, throttle:120,1 |

---

### 8. Secrets Management

| Secret | Config Key | Required |
|--------|-----------|---------|
| `ZYLOS_SECRET` | `titan_core.zylos.secret` | Yes — callbacks rejected without it |
| `ZYLOS_ENDPOINT` | `titan_core.zylos.endpoint` | Yes — skill dispatch disabled without it |

Never commit these values. Use `.env` only.

---

### 9. Audit Trail

Every significant Titan operation writes to `tz_audit_log` via `AuditTrail::recordEntry()`:
- Signal dispatch (success + failure)
- State transitions
- Approval decisions

The `TitanCoreActivity` event additionally broadcasts to `titan.core.activity` for real-time monitoring.
