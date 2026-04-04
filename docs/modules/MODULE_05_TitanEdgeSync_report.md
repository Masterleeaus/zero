# MODULE 05 — TitanEdgeSync: Offline Execution Sync Engine
**Status:** installed  
**Installed at:** 2026-04-03  

---

## Overview

TitanEdgeSync provides a robust offline-first execution and synchronisation system for field technicians. Technicians can complete jobs, fill checklists, capture evidence, and record inspection responses without network connectivity, then sync back to the server when connection is restored.

---

## Architecture

### Sync Lifecycle

```
Device (offline) → Edge Sync Queue → Conflict Detection → Payload Processor → Server Models
                                             ↓
                                   EdgeSyncConflict (if conflict)
                                             ↓
                                   EdgeConflictResolverService
                                             ↓
                                   Signal Dispatcher → Titan Signals
```

### Operation Processing Order

Operations in a batch are processed in `client_created_at` order (ascending). Null timestamps are sorted last.

---

## Files Created

### Migration
- `database/migrations/2026_04_03_900100_create_edge_sync_tables.php`
  - `edge_sync_queues` — incoming operations from devices
  - `edge_sync_conflicts` — conflict records per queue item
  - `edge_device_sessions` — registered device state + sync cursor
  - `edge_sync_log` — per-batch audit log

### Models
| Model | Table | Purpose |
|---|---|---|
| `App\Models\Sync\EdgeSyncQueue` | `edge_sync_queues` | Single offline operation awaiting sync |
| `App\Models\Sync\EdgeSyncConflict` | `edge_sync_conflicts` | Conflict record for a queue item |
| `App\Models\Sync\EdgeDeviceSession` | `edge_device_sessions` | Registered device + sync cursor |
| `App\Models\Sync\EdgeSyncLog` | `edge_sync_log` | Batch audit log |

All models use `BelongsToCompany` trait for multi-tenant scoping.

### Services
| Service | Purpose |
|---|---|
| `App\Services\Sync\EdgeSyncService` | Main orchestrator (register, ingest, process, detect, pull) |
| `App\Services\Sync\EdgeConflictResolverService` | Auto-resolves conflicts using per-type strategy |
| `App\Services\Sync\EdgeSyncPayloadProcessor` | Applies database mutations for each operation type |

### Events
| Event | Fired when |
|---|---|
| `App\Events\Sync\EdgeBatchSynced` | Complete batch processed successfully |
| `App\Events\Sync\EdgeConflictDetected` | Conflict found during processing |
| `App\Events\Sync\EdgeConflictResolved` | Conflict resolved (by user/system/ai) |
| `App\Events\Sync\EdgeSyncFailed` | Operation failed after exhausting retries |

### Listener
| Listener | Listens to |
|---|---|
| `App\Listeners\Sync\RecordSyncEventOnTrustLedger` | `EdgeBatchSynced` — records to TrustWorkLedger (Module 03) or structured audit log |

### API Controller
`App\Http\Controllers\Api\Sync\EdgeSyncController`

---

## API Routes

All routes are under the `auth:api` middleware (Laravel Passport).

| Method | Path | Purpose |
|---|---|---|
| POST | `/api/sync/register` | Register or refresh device session |
| POST | `/api/sync/push` | Push batch of offline operations |
| GET  | `/api/sync/pull` | Fetch server-side delta since cursor |
| POST | `/api/sync/acknowledge` | Advance device sync cursor |
| GET  | `/api/sync/conflicts` | List unresolved conflicts |
| POST | `/api/sync/conflicts/{id}/resolve` | Resolve a specific conflict |

---

## Conflict Detection

Conflicts are detected by comparing `client_created_at` against `updated_at` of the server record:

- If `server.updated_at > client_created_at` → **version_mismatch**
- If subject no longer exists → **deleted_subject**

### Default Resolution Strategies

| Operation Type | Default Strategy |
|---|---|
| `job_update` | `server_wins` |
| `checklist_response` | `merge` |
| `inspection_response` | `merge` |
| `evidence_upload` | `merge` |
| `job_complete` | `manual` |
| `signature_capture` | `manual` |

---

## Signals Emitted

| Signal | When |
|---|---|
| `sync.batch_received` | Batch ingestion starts |
| `sync.conflict_detected` | Conflict found in queue item |
| `sync.conflict_resolved` | Conflict resolved |
| `sync.batch_complete` | Batch fully processed |

---

## Integration Points

| System | Integration |
|---|---|
| `ServiceJob` | `applyJobUpdate()`, `applyJobCompletion()`, `applySignatureCapture()` |
| `ChecklistRun` / `ChecklistResponse` | `applyChecklistResponse()` — upserts responses, refreshes completion counts |
| `InspectionInstance` / `InspectionResponse` | `applyInspectionResponse()` |
| `SignalDispatcher` | Emits `sync.*` signals for all lifecycle events |
| Module 03 (TrustWorkLedger) | `RecordSyncEventOnTrustLedger` listener — delegates to `TrustLedgerService` when bound, falls back to structured log |
| `BelongsToCompany` | All sync models scoped by company_id |

---

## Device Identity

`device_id` is a client-generated UUID. It is unique per user only (not globally). The composite key is `(user_id, device_id)`.

---

## Sync Cursor

The `sync_cursor` on `EdgeDeviceSession` stores the last `edge_sync_queues.id` processed by the device. The `getDeltaForDevice()` method returns server-side records with `id > cursor`.

---

## Tests

| Test | Type | Coverage |
|---|---|---|
| `tests/Unit/Services/Sync/EdgeSyncServiceTest.php` | Unit | Service contracts, processOperation, detectConflicts |
| `tests/Unit/Services/Sync/EdgeConflictResolverServiceTest.php` | Unit | autoResolve, strategy dispatch |
| `tests/Feature/Api/Sync/EdgeSyncControllerTest.php` | Feature | All 6 API endpoints, auth guard, validation |

---

## FSM Module Status

`fsm_module_status.json` updated: `titan_edge_sync` → `installed`
