# Titan MCP Canonical Layer

> **Registry class:** `App\TitanCore\MCP\McpCapabilityRegistry`  
> **Transport model:** Fully in-app HTTP (Laravel routes) — no external MCP server process required  
> **Route file:** `routes/core/mcp.routes.php`

---

## Overview

The Titan MCP (Model Context Protocol) layer provides a standardized capability interface for AI agents, external tooling, and internal subsystems to invoke Titan Core services. All capabilities are resolved through a single `POST api/titan/mcp/invoke` endpoint that dispatches to typed handler classes.

---

## Capability Registry

### `App\TitanCore\MCP\McpCapabilityRegistry`

**File:** `app/TitanCore/MCP/McpCapabilityRegistry.php`

**Registered as:** Singleton in `TitanCoreServiceProvider`

**Public API:**

```php
public function all(): array       // All capability definitions
public function get(string $name): ?array  // Single capability by name
public function names(): array     // Capability name list only
```

---

## Capabilities (7)

### `titan.ai.complete`

| Field | Value |
|-------|-------|
| Handler | `App\TitanCore\MCP\Handlers\AiCompleteHandler` |
| Description | Execute an AI completion through TitanAIRouter |
| Auth required | Yes |
| Tenancy enforced | Yes |
| Approval-aware | Yes |
| Queue | `titan-ai` |
| Rate limit | 60/min |

**Parameters:**

```json
{
  "company_id": "int (required)",
  "session_id": "string (required)",
  "input":      "string (required)",
  "stage":      "string (optional, default: suggestion)",
  "context":    "object (optional)"
}
```

**Notes:** All requests routed through `TitanAIRouter::execute()`. Budget enforced before execution. Memory recalled before and stored after. Signal recorded on completion.

---

### `titan.memory.store`

| Field | Value |
|-------|-------|
| Handler | `App\TitanCore\MCP\Handlers\MemoryStoreHandler` |
| Description | Store a memory entry via TitanMemoryService |
| Auth required | Yes |
| Tenancy enforced | Yes |
| Approval-aware | No |
| Queue | `titan-ai` |
| Rate limit | 120/min |

**Parameters:**

```json
{
  "company_id":       "int (required)",
  "user_id":          "int (optional)",
  "session_id":       "string (required)",
  "type":             "string (optional, default: general)",
  "content":          "string (required)",
  "importance_score": "float (optional)",
  "expires_at":       "string|null (optional)"
}
```

**Backwards-compat params accepted:** `key` (alias for `session_id`), `payload` (JSON alias for `content`).

**Returns:** `{ "ok": bool, "session_id": string, "data": object }`

---

### `titan.memory.recall`

| Field | Value |
|-------|-------|
| Handler | `App\TitanCore\MCP\Handlers\MemoryRecallHandler` |
| Description | Recall memory entries via TitanMemoryService |
| Auth required | Yes |
| Tenancy enforced | Yes |
| Approval-aware | No |
| Queue | (synchronous) |
| Rate limit | 120/min |

**Parameters:**

```json
{
  "company_id":    "int (required)",
  "session_id":    "string (required)",
  "query":         "string (optional, enables semantic search)",
  "type":          "string (optional, filter by type)",
  "limit":         "int (optional, default: 20)",
  "semantic_limit": "int (optional, default: 5)"
}
```

**Backwards-compat params accepted:** `key` (alias for `session_id`).

**Returns:** `{ "ok": bool, "session_id": string, "data": { "memories": array, "semantic_results": array } }`

---

### `titan.signal.dispatch`

| Field | Value |
|-------|-------|
| Handler | `App\TitanCore\MCP\Handlers\SignalDispatchHandler` |
| Description | Dispatch a Titan signal through the signal pipeline |
| Auth required | Yes |
| Tenancy enforced | Yes |
| Approval-aware | Yes |
| Queue | `titan-signals` |
| Rate limit | 60/min |

**Parameters:**

```json
{
  "company_id":      "int (required)",
  "process_payload": "object (optional)",
  "signal_payload":  "object (optional)"
}
```

**Notes:** Calls `SignalBridge::recordAndIngest()`. Approval gate active when signal type requires it.

---

### `titan.skill.dispatch`

| Field | Value |
|-------|-------|
| Handler | `App\TitanCore\MCP\Handlers\SkillDispatchHandler` |
| Description | Dispatch a skill execution through ZylosBridge |
| Auth required | Yes |
| Tenancy enforced | Yes |
| Approval-aware | Yes |
| Queue | `titan-skills` |
| Rate limit | 30/min |

**Parameters:**

```json
{
  "skill":   "string (required, skill slug)",
  "payload": "object (optional, forwarded to Zylos)"
}
```

**Notes:** Delegates to `ZylosBridge::dispatch($skill, $payload)`. HMAC-signed HTTP POST to Zylos runtime.

---

### `titan.skill.status`

| Field | Value |
|-------|-------|
| Handler | `App\TitanCore\MCP\Handlers\SkillStatusHandler` |
| Description | Query the status of a dispatched skill execution |
| Auth required | Yes |
| Tenancy enforced | Yes |
| Approval-aware | No |
| Queue | (synchronous) |
| Rate limit | 120/min |

**Parameters:**

```json
{
  "execution_id": "string (required)"
}
```

**Notes:** Delegates to `ZylosBridge::executionStatus($executionId)`.

---

### `titan.skill.list`

| Field | Value |
|-------|-------|
| Handler | `App\TitanCore\MCP\Handlers\SkillListHandler` |
| Description | List available skills from the Zylos runtime |
| Auth required | Yes |
| Tenancy enforced | Yes |
| Approval-aware | No |
| Queue | (synchronous) |
| Rate limit | 30/min |

**Parameters:** None required.

**Notes:** Delegates to `ZylosBridge::list()`.

---

## Handlers Directory

```
app/TitanCore/MCP/Handlers/
├── AiCompleteHandler.php
├── MemoryRecallHandler.php
├── MemoryStoreHandler.php
├── SignalDispatchHandler.php
├── SkillDispatchHandler.php
├── SkillListHandler.php
└── SkillStatusHandler.php
```

---

## Routes

**File:** `routes/core/mcp.routes.php`

### Authenticated MCP Endpoints

```
Middleware: auth:sanctum, EnforceTitanTenancy, throttle:60,1
Prefix:     api/titan/mcp
```

| Method | URI | Name | Controller Method |
|--------|-----|------|------------------|
| `GET` | `api/titan/mcp/capabilities` | `titan.mcp.capabilities` | `McpServerController@capabilities` |
| `POST` | `api/titan/mcp/invoke` | `titan.mcp.invoke` | `McpServerController@invoke` |

### Zylos Skill Callback

```
Middleware: ValidateZylosSignature, throttle:120,1
Prefix:     api/titan/signal
```

| Method | URI | Name | Controller Method |
|--------|-----|------|------------------|
| `POST` | `api/titan/signal/callback` | `titan.signal.callback` | `McpServerController@skillCallback` |

**Note:** The callback route does **not** require `auth:sanctum`. It is protected exclusively by HMAC-SHA256 signature validation (`ValidateZylosSignature` middleware).

---

## Transport Model

Titan MCP operates as a **fully in-app HTTP transport**. There is no separate MCP server process or daemon. All capability invocations pass through standard Laravel HTTP request/response cycles.

| Transport | Status | Config |
|-----------|--------|--------|
| In-app HTTP (Laravel routes) | ✅ Active | Built-in |
| External MCP HTTP URL | ⚙️ Env-dependent | `MCP_HTTP_URL` |
| WebSocket (MCP WS) | ⚙️ Env-dependent | `MCP_WS_URL` |

When `MCP_HTTP_URL` is set, the `capabilities` response advertises the external URL for MCP clients. When empty, the capability manifest is still served but without an external transport hint.

---

## Auth and Tenancy

### Authentication

All capability routes require `auth:sanctum`. Requests without a valid Bearer token or session cookie receive a `401 Unauthorized` response.

### Tenancy (`EnforceTitanTenancy` middleware)

- Extracts `company_id` from the authenticated user's context.
- Rejects requests where the authenticated user does not belong to the claimed `company_id`.
- Applied to all `api/titan/mcp/*` routes. Not applied to the Zylos callback route (HMAC-protected instead).

### Throttling

| Route group | Limit |
|-------------|-------|
| MCP capabilities/invoke | 60 requests/minute |
| Zylos callback | 120 requests/minute |

Per-capability rate limits are defined in `McpCapabilityRegistry` and enforced at the handler level.

---

## Additional MCP-Adjacent Routes

**File:** `routes/core/titan_core.routes.php`

```
Middleware: auth, updateUserActivity, throttle:120,1
Prefix:     (varies)
Name prefix: titan.core.*, titan.memory.*
```

| Route | Name | Purpose |
|-------|------|---------|
| `GET /titan/core/status` | `titan.core.status` | Core status dashboard |
| `GET /titan/core/health` | `titan.core.health` | Health API |
| `GET /titan/core/runtime` | `titan.core.runtime` | Runtime status |
| `POST /titan/memory/recall` | `titan.memory.recall` | Direct memory recall (Sanctum) |
| `POST /titan/memory/store` | `titan.memory.store` | Direct memory store (Sanctum) |

These routes use `App\Titan\Core\Mcp\Tools\MemoryRecallTool` and `MemoryStoreTool` directly, separate from the MCP capability dispatch path.
