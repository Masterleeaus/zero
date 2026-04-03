# TITAN MEMORY RUNTIME — FINAL SPECIFICATION

**Version:** Prompt 6  
**Status:** Phase 6.5 Validated

---

## Architecture

```
TitanMemoryService (MemoryContract)
    │
    ├─ store(key, payload, company_id)
    │      └─ Cache::put("titan.memory.{company_id}.{key}", payload, TTL)
    │
    ├─ recall(key, company_id)
    │      └─ Cache::get("titan.memory.{company_id}.{key}")
    │
    ├─ snapshot(key)
    │      └─ MemoryManager::snapshot(key)
    │             └─ SessionHandoffManager::export(key)
    │
    └─ expire(key, company_id)
           └─ Cache::forget("titan.memory.{company_id}.{key}")
```

---

## Tenancy Enforcement

- All cache keys are prefixed with `titan.memory.{company_id}`.
- A `company_id = null` falls back to `titan.memory.global` (only for internal bootstrapping).
- In production, `company_id` MUST always be non-null. Enforced by `EnforceTitanTenancy` middleware.
- No cross-tenant key access is possible because cache key is constructed server-side.

---

## Memory Types

| Type | Description | TTL |
|------|-------------|-----|
| **Ephemeral** | Per-request context | `TITAN_MEMORY_TTL` (default 3600s) |
| **Session** | Cross-session handoff via `SessionHandoffManager` | Session lifetime |
| **Snapshot** | Rewind-compatible frozen state via `MemorySnapshot` | Permanent (DB-backed) |

---

## Configuration

| Env Variable | Config Key | Default | Purpose |
|-------------|-----------|---------|---------|
| `TITAN_MEMORY_TTL` | `titan_core.memory.ttl` | 3600 | Cache entry TTL in seconds |
| `TITAN_MEMORY_MAX_TOKENS` | `titan_core.memory.max_tokens` | 8192 | Max tokens injected into context |
| `TITAN_MEMORY_DRIVER` | `titan_core.memory.driver` | `cache` | Storage backend |

---

## MemoryContract Interface

```php
interface MemoryContract
{
    public function store(string $key, array $payload, ?int $companyId = null): void;
    public function recall(string $key, ?int $companyId = null): ?array;
    public function snapshot(string $key): array;
    public function expire(string $key, ?int $companyId = null): void;
}
```

Implemented by: `App\TitanCore\Zero\Memory\TitanMemoryService`

---

## MCP Memory Capabilities

| Capability | Handler |
|-----------|---------|
| `titan.memory.store` | `MemoryStoreHandler` |
| `titan.memory.recall` | `MemoryRecallHandler` |

Both capabilities enforce `company_id` tenancy via `EnforceTitanTenancy` middleware.

---

## Router Recall Integration

`ZeroCoreManager::decide()` calls `MemoryManager::snapshot()` before each decision to inject prior context. This snapshot includes:
- `session` state from `SessionHandoffManager::export()`
- `scope: tenant`

---

## Snapshot Linkage

`MemorySnapshot` is consumed by the Rewind subsystem:
- `RewindSnapshot` (DB model) stores the frozen state at correction time.
- `RewindEngine` hydrates context from the snapshot during replay.
- `RewindLink` connects original and corrected process states.

---

## Session Handoff

`SessionHandoffManager` preserves AI context across session boundaries:
1. At session end: `export(key)` serialises current context.
2. At session start: context is re-injected into `MemoryManager::snapshot()`.
3. Ensures continuity for multi-session AI conversations.
