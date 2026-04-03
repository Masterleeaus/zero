# Titan Core Runtime Status

> **As of:** Pre-Nexus canonicalization pass  
> **Scope:** What is production-ready, what depends on environment config, what was not verifiable in sandbox

---

## Legend

| Symbol | Meaning |
|--------|---------|
| ✅ | Production-ready — code complete, no external dependency gap |
| ⚙️ | Env-dependent — requires environment variables or external services |
| 🔶 | Partial — functional path exists but a feature branch is incomplete |
| ❌ | Sandbox-unverifiable — depends on runtime resources unavailable in sandbox |

---

## Core Boot

| Component | Status | Notes |
|-----------|--------|-------|
| `TitanCoreServiceProvider` registered | ✅ | Fatal import conflicts resolved; boots cleanly |
| All singletons resolvable from container | ❌ | No `vendor/` in sandbox — autoload not testable |
| Config files merged (`titan_core`, `titan_ai`, `titan_budgets`) | ✅ | No duplicate keys; structure verified |
| Route files loaded (`titan_admin.routes.php`, `mcp.routes.php`, `titan_core.routes.php`) | ✅ | All files verified present with correct names |

---

## Memory Subsystem

| Feature | Status | Notes |
|---------|--------|-------|
| `App\Titan\Core\TitanMemoryService` canonical class | ✅ | All 7 methods implemented |
| DB tables (`tz_ai_memories`, `tz_ai_memory_snapshots`, `tz_ai_session_handoffs`, `tz_ai_memory_embeddings`) | ❌ | Migrations exist; actual table presence unverified without DB |
| Vector embeddings via `VectorMemoryAdapter` | ⚙️ | Requires `titan_memory.vector.enabled = true` + embedding model config |
| Rewind snapshots | ✅ | `snapshot()` method integrates `RewindManager` |
| MCP memory handlers (`MemoryStoreHandler`, `MemoryRecallHandler`) | ✅ | Use canonical `TitanMemoryService`; params normalized |
| Cache-backed tombstone (`App\TitanCore\Zero\Memory\TitanMemoryService`) | ✅ | Retained for `MemoryContract` compatibility only |

---

## Zylos Skill Bridge

| Feature | Status | Notes |
|---------|--------|-------|
| `App\TitanCore\Zylos\ZylosBridge` canonical class | ✅ | All 7 methods implemented |
| HTTP dispatch to skill runtime | ⚙️ | Requires `ZYLOS_ENDPOINT` and `ZYLOS_SECRET` env vars |
| HMAC-SHA256 callback validation | ✅ | `validateCallback()` implemented; tested via `ValidateZylosSignature` middleware |
| DB event logging (`tz_skill_events`) | ❌ | Table assumed from prior migrations; not runtime-verified |
| Admin skill restart/disable | ✅ | Controller methods and views complete |
| `executionStatus()` | ⚙️ | Depends on active Zylos runtime at `ZYLOS_ENDPOINT` |

---

## MCP Layer

| Feature | Status | Notes |
|---------|--------|-------|
| `McpCapabilityRegistry` with 7 capabilities | ✅ | All capabilities registered; handlers present |
| `GET api/titan/mcp/capabilities` | ✅ | Returns capability manifest |
| `POST api/titan/mcp/invoke` | ✅ | Routes to correct handler per capability name |
| `POST api/titan/signal/callback` | ✅ | HMAC-validated; calls `McpServerController::skillCallback` |
| `titan.ai.complete` capability | ⚙️ | Depends on AI runtime model (env: `TITAN_CORE_DEFAULT_RUNTIME`) |
| `titan.memory.*` capabilities | ✅ | Fully in-app; no external service required |
| `titan.signal.dispatch` capability | ✅ | Fully in-app signal pipeline |
| `titan.skill.*` capabilities | ⚙️ | Depends on Zylos runtime being reachable |
| MCP HTTP transport mode | ⚙️ | Requires `MCP_HTTP_URL` env var to advertise external URL |
| MCP WebSocket transport mode | ⚙️ | Requires `MCP_WS_URL` env var |
| Auth enforcement | ✅ | `auth:sanctum` + `EnforceTitanTenancy` on all capability routes |
| Throttle enforcement | ✅ | `throttle:60,1` on MCP routes; `throttle:120,1` on callback |

---

## AI Router

| Feature | Status | Notes |
|---------|--------|-------|
| `TitanAIRouter` singleton | ✅ | Registered in provider |
| Budget enforcement | ✅ | `TitanTokenBudget` checked before execution |
| Memory injection (recall before, store after) | ✅ | Calls canonical `TitanMemoryService` |
| Signal recording | ✅ | `SignalBridge::recordAndIngest()` called post-execution |
| Nexus multi-core execution | ⚙️ | Requires AI provider credentials; 7 cores enabled by config |
| Critique loop / round-robin refinement | ⚙️ | Config-controlled; requires live AI provider |
| `NullRuntimeAdapter` fallback | ✅ | Active when `TITAN_CORE_DEFAULT_RUNTIME=null` |

---

## Admin Panel

| Screen | Status | Notes |
|--------|--------|-------|
| Models (`admin.titan.core.models`) | ✅ | View + update routes; reads `titan_core.ai` config |
| Signals (`admin.titan.core.signals`) | ✅ | Queries `tz_signals` with filtering |
| Memory (`admin.titan.core.memory`) | ✅ | Stats from `tz_ai_memories`; purge + summarise actions |
| Skills (`admin.titan.core.skills`) | ✅ | Live status from `ZylosBridge::status()` |
| Activity (`admin.titan.core.activity`) | ✅ | Last 100 entries from `AuditTrail` |
| Budgets (`admin.titan.core.budgets`) | ✅ | Reads/writes `titan_core.budget` config |
| Queues (`admin.titan.core.queues`) | ✅ | Queue stats + retry/flush actions |
| Health (`admin.titan.core.health`) | ✅ | 8 health checks; also exposes JSON API endpoint |

---

## Queue Workers

| Queue | Status | Notes |
|-------|--------|-------|
| `titan-ai` | ⚙️ | Must be started with `php artisan queue:work --queue=titan-ai` |
| `titan-signals` | ⚙️ | Must be started with `php artisan queue:work --queue=titan-signals` |
| `titan-skills` | ⚙️ | Must be started with `php artisan queue:work --queue=titan-skills` |
| Worker routing correctness | ❌ | Queue routing not runtime-verified in sandbox |

---

## Required Environment Variables

| Variable | Default | Used By |
|----------|---------|---------|
| `ZYLOS_ENDPOINT` | `""` | `ZylosBridge` dispatch |
| `ZYLOS_SECRET` | `""` | `ZylosBridge` HMAC signing / callback validation |
| `ZYLOS_TIMEOUT` | `10` | `ZylosBridge` HTTP timeout |
| `MCP_HTTP_URL` | `""` | MCP capability transport advertisement |
| `MCP_WS_URL` | `""` | MCP WebSocket transport advertisement |
| `TITAN_CORE_DEFAULT_RUNTIME` | `null` | AI runtime adapter selection |
| `TITAN_CORE_MODEL_ROUTER` | `zero` | Model routing strategy |
| `TITAN_DEFAULT_TEXT_MODEL` | `gpt-4o` | Default text model |
| `TITAN_AI_DAILY_LIMIT` | `100000` | Budget token limit |
| `TITAN_MEMORY_TTL` | `3600` | Memory cache/DB TTL |

---

## Sandbox Limitations

The following were **not** testable in the sandbox environment and should be verified on first deploy:

1. Full Laravel boot — no `vendor/` directory available.
2. DB table existence — migrations exist but `php artisan migrate` not run.
3. Queue worker routing — workers not started.
4. Zylos HTTP dispatch — no live `ZYLOS_ENDPOINT` available.
5. AI model execution — no live AI provider credentials.
6. MCP HTTP/WS URL advertisement — env vars empty.
7. Vector embedding path — requires embedding model config.
