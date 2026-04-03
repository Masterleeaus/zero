# TITAN REWIND MEMORY COMPATIBILITY

## Overview

TitanMemoryService is rewind-aware. Memory snapshots can be linked to
rewind events, enabling corrected states to supersede earlier states
while preserving the full audit trail.

---

## Rewind-Compatible Snapshots

`TitanMemoryService::snapshot()` creates records in `tz_ai_memory_snapshots`
with an optional `rewind_ref` column linking to `tz_rewind_snapshots`.

```php
$snapshot = $memoryService->snapshot(
    companyId: $companyId,
    sessionId: $sessionId,
    meta: ['rewind_ref' => $rewindSnapshotKey, 'user_id' => $userId]
);
```

The snapshot contains:
- The exported session state (via `SessionHandoffManager::export()`)
- The recalled memories at that point in time
- The `rewind_ref` linking to the rewind event
- The `snapshot_key` for direct lookup

---

## Corrected State Superseding

When a rewind occurs and a process moves from `processed → rewinding`:

1. The existing memory snapshot is preserved (original state traceable)
2. A new snapshot is created after correction with the same `session_id`
3. The new snapshot carries the updated `rewind_ref`
4. `TitanMemoryService::recall()` returns memories ordered by `importance_score DESC, created_at DESC`
   — the most recent corrected memories surface first

---

## Audit Trail Preservation

Every memory operation writes an audit trail entry via `AuditTrail::recordEntry()`:

| Event | Audit Key |
|---|---|
| Memory stored | `titan.memory.stored` |
| Memory recalled (MCP) | `titan.memory.recall.mcp` |
| Memory stored (MCP) | `titan.memory.store.mcp` |
| Memory forgotten | `titan.memory.forgotten` |
| Snapshot created | `titan.memory.snapshot` |

---

## Original + Corrected State Traceability

```
tz_ai_memory_snapshots
  ├── snapshot_id: 1, session_id: sess-abc, rewind_ref: null  ← original
  └── snapshot_id: 2, session_id: sess-abc, rewind_ref: rw-xyz  ← corrected

tz_rewind_snapshots
  └── snapshot_key: rw-xyz, company_id: 42, before_json: ..., after_json: ...
```

The `rewind_ref` in `tz_ai_memory_snapshots` traces directly to the
`snapshot_key` in `tz_rewind_snapshots`.

---

## Context Hydration Rewind-Awareness

`TitanMemoryService::hydrateContext()` always retrieves the most recent
non-expired memories. If a rewind has occurred:

1. Memories created after the rewind are higher `created_at` and surface first
2. Memories from before the rewind remain in the store but rank lower
3. `importance_score` can be used to weight corrected memories higher

---

## Config

Enable rewind integration:

```env
TITAN_MEMORY_REWIND_ENABLED=true
TITAN_MEMORY_AUTO_SNAPSHOT=true
```

Or in `config/titan_memory.php`:

```php
'rewind' => [
    'enabled' => true,
    'auto_snapshot_on_rewind' => true,
],
```

---

## Related Tables

| Table | Role |
|---|---|
| `tz_rewind_snapshots` | Canonical rewind record (before/after JSON) |
| `tz_ai_memory_snapshots` | Memory-layer checkpoint (linked via rewind_ref) |
| `tz_ai_memories` | Live memory records (survive rewind) |
| `tz_audit_log` | Audit trail for all operations |
