# Titan Memory Canonical Path

> **Canonical class:** `App\Titan\Core\TitanMemoryService`  
> **Deprecated path:** `App\TitanCore\Zero\Memory\TitanMemoryService` (tombstone)

---

## Overview

Titan Core uses a single DB-backed memory service for all AI context storage, session continuity, and rewind checkpointing. The canonical implementation lives at `App\Titan\Core\TitanMemoryService` and is the **only** class that should be injected into MCP handlers, the AI router, admin controllers, or any new code.

---

## Canonical Class

### `App\Titan\Core\TitanMemoryService`

**File:** `app/Titan/Core/TitanMemoryService.php`

**Registered as:** Singleton in `TitanCoreServiceProvider`

#### Constructor

```php
public function __construct(
    MemoryManager            $memoryManager,
    SessionHandoffManager    $sessionHandoff,
    KnowledgeManager         $knowledgeManager,
    KnowledgeScopeResolver   $scopeResolver,
    RewindManager            $rewindManager,
    VectorMemoryAdapter      $vectorAdapter,
    AuditTrail               $auditTrail,
)
```

All dependencies are resolved from the container. Do not instantiate directly.

---

## Public API

### `store()`

```php
public function store(
    int    $companyId,
    int    $userId,
    string $sessionId,
    string $type,
    string $content,
    array  $context = []
): array
```

Stores a memory entry in `tz_ai_memories`. Optionally creates a vector embedding when `titan_memory.vector.enabled` is `true`. Records to audit trail.

**Returns:** `['ok' => bool, 'id' => int, 'session_id' => string]`

---

### `recall()`

```php
public function recall(
    int    $companyId,
    string $sessionId,
    array  $options = []
): array
```

Recalls memories for a session. Options:

| Key | Type | Default | Purpose |
|-----|------|---------|---------|
| `query` | `string` | `null` | Semantic search query |
| `type` | `string` | `null` | Filter by memory type |
| `limit` | `int` | `20` | Max DB results |
| `semantic_limit` | `int` | `5` | Max vector results |

**Returns:** `['ok' => bool, 'session_id' => string, 'data' => ['memories' => array, 'semantic_results' => array]]`

---

### `forget()`

```php
public function forget(int $companyId, int $memoryId): array
```

Soft-deletes a memory entry (`deleted_at` set). Records deletion to audit trail.

**Returns:** `['ok' => bool, 'id' => int]`

---

### `summarize()`

```php
public function summarize(int $companyId, string $sessionId): array
```

Distills the top 10 most important memories for a session into a summary string. Used by the admin `memorySummarise` action.

**Returns:** `['ok' => bool, 'summary' => string, 'session_id' => string]`

---

### `snapshot()`

```php
public function snapshot(
    int    $companyId,
    string $sessionId,
    array  $meta = []
): array
```

Creates a rewind-compatible snapshot in `tz_ai_memory_snapshots`. The snapshot is linked via `rewind_ref` for later restoration by `RewindManager`.

**Returns:** `['ok' => bool, 'snapshot_id' => int, 'rewind_ref' => string]`

---

### `hydrateContext()`

```php
public function hydrateContext(array $envelope): array
```

Full context hydration for an AI envelope. Recalls memories, resolves knowledge scope, and returns a structured context pack for injection into the AI prompt.

**Required envelope keys:** `company_id`, `session_id`, `input`

**Returns:** `['ok' => bool, 'context' => array, 'memories' => array, 'knowledge' => array, 'scope' => string]`

---

### `storeHandoff()`

```php
public function storeHandoff(
    int    $companyId,
    int    $userId,
    string $sessionId,
    array  $context = []
): array
```

Stores a session handoff record in `tz_ai_session_handoffs` for cross-session continuity (e.g., when an AI session transitions between agents or channels).

**Returns:** `['ok' => bool, 'handoff_id' => int, 'session_id' => string]`

---

## Database Tables

### `tz_ai_memories`

Primary memory store.

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint, PK | Auto-increment |
| `company_id` | int | Tenant boundary — always required |
| `user_id` | int, nullable | User who created the memory |
| `session_id` | string | Session identifier |
| `type` | string | Memory type (e.g., `general`, `instruction`, `fact`) |
| `content` | text | Memory content |
| `embedding_reference` | string, nullable | Reference to vector store entry |
| `importance_score` | float, nullable | Recall priority weight |
| `expires_at` | timestamp, nullable | TTL expiry |
| `deleted_at` | timestamp, nullable | Soft-delete marker |

**Indexes:** `company_session`, `company_type`, `company_importance`, `company_expires`

---

### `tz_ai_memory_snapshots`

Rewind-compatible snapshots.

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint, PK | |
| `company_id` | int | Tenant boundary |
| `user_id` | int, nullable | |
| `session_id` | string | |
| `type` | string | Snapshot type |
| `content` | longText | Full snapshot payload |
| `rewind_ref` | string, nullable | Links to `RewindManager` checkpoint |
| `embedding_reference` | string, nullable | |
| `importance_score` | float, nullable | |
| `expires_at` | timestamp, nullable | |

**Indexes:** `company_session`, `company_rewind`, `company_type`

---

### `tz_ai_memory_embeddings`

Vector embedding index (when vector memory is enabled).

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint, PK | |
| `company_id` | int | |
| `session_id` | string | |
| `embedding_reference` | string, unique | Foreign key into vector store |
| `importance_score` | float, nullable | |
| `expires_at` | timestamp, nullable | |

**Indexes:** `company_session`, `company_type`, `embedding_reference`

---

### `tz_ai_session_handoffs`

Cross-session handoff records.

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint, PK | |
| `company_id` | int | |
| `user_id` | int, nullable | |
| `session_id` | string | |
| `type` | string | |
| `content` | longText | Handoff payload |
| `expires_at` | timestamp, nullable | |

**Indexes:** `company_session`, `company_type`, `company_expires`

---

## Vector Memory

Vector search is managed by `App\Titan\Core\Vector\VectorMemoryAdapter`.

- **Enabled by:** `titan_memory.vector.enabled = true` (env: `TITAN_MEMORY_VECTOR_ENABLED`)
- **Flow:** On `store()`, content is embedded and reference stored in `tz_ai_memory_embeddings`. On `recall()` with a `query`, the adapter performs semantic search and returns `semantic_results`.
- **Disabled behavior:** When vector is disabled, `semantic_results` is always an empty array.

---

## Rewind Compatibility

Snapshots created by `snapshot()` carry a `rewind_ref` field that can be linked to a process via:

```php
$signalContract->withRewindRef($signal, $rewindRef);
$processContract->linkRewind($processId, $rewindRef);
```

`RewindManager` uses this reference to restore session memory state to the snapshot point.

---

## Tenancy Scoping

- **Primary tenant boundary:** `company_id` (always required, enforced at every API method).
- `user_id` is optional and used for audit/attribution only — it is never used as a tenant boundary.
- `team_id` is not used by the memory service; `TitanAIRouter` normalizes any `team_id`-keyed envelopes to `company_id` before calling memory methods.

---

## Deprecated Path

### `App\TitanCore\Zero\Memory\TitanMemoryService`

**File:** `app/TitanCore/Zero/Memory/TitanMemoryService.php`

**Status:** Tombstone — retained for `MemoryContract` interface compatibility only.

**Do NOT use for:**
- MCP handler injection
- AI router memory operations
- Admin panel data
- Any new feature code

**Implements:** `App\TitanCore\Contracts\MemoryContract`

**Backing:** Laravel cache (not DB). No rewind support. No vector support. No audit trail.

**API difference:**

```php
// Deprecated (cache-backed, string keys):
public function store(string $key, array $payload, ?int $companyId = null): void
public function recall(string $key, ?int $companyId = null): ?array
public function snapshot(string $key): array
public function expire(string $key, ?int $companyId = null): void

// Canonical (DB-backed, typed parameters):
public function store(int $companyId, int $userId, string $sessionId, string $type, string $content, array $context = []): array
public function recall(int $companyId, string $sessionId, array $options = []): array
// ... plus forget(), summarize(), snapshot(), hydrateContext(), storeHandoff()
```

---

## Migration Files

| File | Creates |
|------|---------|
| `database/migrations/2026_04_03_200001_create_tz_ai_memories_table.php` | `tz_ai_memories` |
| `database/migrations/2026_04_03_200002_create_tz_ai_memory_embeddings_table.php` | `tz_ai_memory_embeddings` |
| `database/migrations/2026_04_03_200003_create_tz_ai_memory_snapshots_table.php` | `tz_ai_memory_snapshots` |
| `database/migrations/2026_04_03_200004_create_tz_ai_session_handoffs_table.php` | `tz_ai_session_handoffs` |
