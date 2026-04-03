# TITAN MEMORY ARCHITECTURE

## Overview

TitanMemoryService is the canonical memory entrypoint for TitanCore. It is
the **only** authorised path for reading and writing AI memory records.

All memory operations are:
- Scoped by `company_id` (tenant boundary)
- Integrated with Signal and Rewind
- Audit-trailed
- MCP-safe

---

## Service Location

```
app/Titan/Core/TitanMemoryService.php
```

Namespace: `App\Titan\Core`

Registered as a singleton in `TitanCoreServiceProvider`.

---

## Canonical Methods

| Method | Purpose |
|---|---|
| `store()` | Persist a memory record to `tz_ai_memories` |
| `recall()` | Retrieve memories for a session (+ optional semantic search) |
| `forget()` | Soft-delete a memory record (audit trail preserved) |
| `summarize()` | Distil top memories into a context string |
| `snapshot()` | Create a rewind-compatible memory checkpoint |
| `hydrateContext()` | Build a full AI context package from memory + knowledge |
| `storeHandoff()` | Persist a session handoff to `tz_ai_session_handoffs` |

---

## Bridged Components

TitanMemoryService wraps and bridges the following existing internals:

| Component | Namespace | Role |
|---|---|---|
| MemoryManager | `App\TitanCore\Zero\Memory` | Session snapshot creation |
| MemorySnapshot | `App\TitanCore\Zero\Memory` | Immutable snapshot value object |
| SessionHandoffManager | `App\TitanCore\Zero\Memory\Session` | Session state export with timestamps |
| KnowledgeManager | `App\TitanCore\Zero\Knowledge` | Knowledge source resolution |
| KnowledgeScopeResolver | `App\TitanCore\Zero\Knowledge` | Scope: site / job / tenant / global |
| VectorMemoryAdapter | `App\Titan\Core\Vector` | Embedding storage + semantic search (substrate only) |
| AuditTrail | `App\Titan\Signals` | Audit entry recording |
| RewindManager | `App\TitanCore\Zero\Rewind` | Rewind link creation |

---

## TitanAIRouter Integration

TitanAIRouter has been updated to inject `TitanMemoryService`:

**Before decision (recall):**
```php
$memoryContext = $this->memoryService->hydrateContext($envelope);
$envelope['_memory_context'] = $memoryContext;
```

**After execution (store):**
```php
$this->memoryService->store($companyId, $userId, $sessionId, 'ai_decision', $content);
```

---

## MCP Memory Tools

Two MCP tools expose memory operations over Sanctum-authenticated HTTP:

| Tool | Route | Class |
|---|---|---|
| `titan.memory.recall` | `POST /api/titan/memory/recall` | `MemoryRecallTool` |
| `titan.memory.store` | `POST /api/titan/memory/store` | `MemoryStoreTool` |

Both tools:
- Require `auth:sanctum` middleware
- Enforce `company_id` from authenticated user
- Write audit trail entries
- Rate-limited (60/min)

---

## Tenancy Model

- `company_id` is always the tenant boundary
- `team_id` is never used as a tenant key in memory
- `user_id` is the actor identity
- All queries `WHERE company_id = ?`

See: `docs/titancore/23_COMPANY_ID_TENANCY_MODEL.md`

---

## Config

```
config/titan_memory.php
```

Key settings:
- `titan_memory.vector.enabled` — activate semantic recall
- `titan_memory.rewind.enabled` — link snapshots to rewind events
- `titan_memory.handoff_ttl_hours` — session handoff expiry
- `titan_memory.importance.*` — importance score defaults by type
