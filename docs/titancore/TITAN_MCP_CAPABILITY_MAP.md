# TITAN MCP CAPABILITY MAP

**Version:** Prompt 6  
**Status:** Active

---

## Overview

The Titan MCP (Model Context Protocol) layer exposes all core Titan capabilities through a unified transport API. This document maps every registered capability, its handler, auth requirements, and queue assignment.

---

## Registered Capabilities

| Name | Description | Auth | Tenancy | Approval | Queue | Rate/min |
|------|-------------|------|---------|----------|-------|---------|
| `titan.ai.complete` | AI completion via TitanAIRouter | ✓ | ✓ | ✓ | titan-ai | 60 |
| `titan.memory.store` | Store memory entry | ✓ | ✓ | — | titan-ai | 120 |
| `titan.memory.recall` | Recall memory entry | ✓ | ✓ | — | — (sync) | 120 |
| `titan.signal.dispatch` | Dispatch signal pipeline | ✓ | ✓ | ✓ | titan-signals | 60 |
| `titan.skill.dispatch` | Dispatch skill via ZylosBridge | ✓ | ✓ | ✓ | titan-skills | 30 |
| `titan.skill.status` | Query skill execution status | ✓ | ✓ | — | — (sync) | 120 |
| `titan.skill.list` | List available Zylos skills | ✓ | ✓ | — | — (sync) | 30 |

---

## HTTP Transport Endpoints

### List Capabilities
```
GET  /api/titan/mcp/capabilities
Auth: Bearer (Sanctum)
Tenancy: company_id required
```

### Invoke Capability
```
POST /api/titan/mcp/invoke
Auth: Bearer (Sanctum)
Tenancy: company_id required
Rate: throttle:60,1

Body:
{
  "capability": "titan.ai.complete",
  "params": {
    "intent": "text.complete",
    "prompt": "...",
    "company_id": 42
  }
}
```

### Zylos Skill Callback
```
POST /api/titan/signal/callback
Auth: X-Zylos-Signature (HMAC-SHA256)
Rate: throttle:120,1
No Sanctum — signature-only auth
```

---

## Security Model

| Endpoint | Mechanism |
|----------|-----------|
| `/api/titan/mcp/*` | auth:sanctum + EnforceTitanTenancy |
| `/api/titan/signal/callback` | ValidateZylosSignature middleware |
| All MCP routes | throttle rate limiting |

- No anonymous access permitted on any MCP endpoint.
- No tenancy leakage — `company_id` is always server-resolved from the authenticated user.
- No approval bypass — approval-aware capabilities check `approval_required` before execution.

---

## MCP Artisan Commands

```bash
# List all registered capabilities
php artisan mcp:server capabilities

# Smoke test: verify all required capabilities and handlers
php artisan mcp:server test

# Health check: config and connectivity status
php artisan mcp:server health
```

---

## Claude Desktop / WebSocket Connection

MCP transport supports:
- **HTTP** — via `/api/titan/mcp/invoke`
- **WebSocket** — deferred to Nexus convergence pass
- **Claude Desktop** — connect via HTTP transport URL

---

## Omni Surface Compatibility

All actions available through MCP are also invocable from Omni surfaces. No controller-only execution paths remain for any capability in this list. Every action respects the approval chain.

---

## Adding New Capabilities

1. Add entry to `McpCapabilityRegistry::all()` with handler class.
2. Create handler in `app/TitanCore/MCP/Handlers/`.
3. Handler MUST delegate to a canonical service (TitanAIRouter, TitanMemoryService, etc.).
4. Run `php artisan mcp:server test` to verify.
