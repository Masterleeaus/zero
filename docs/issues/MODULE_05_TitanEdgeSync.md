# MODULE 05 — TitanEdgeSync: Offline Execution Sync Engine

**Label:** `titan-module` `offline` `sync` `edge` `pwa` `mobile`
**Milestone:** TitanZero Capability Expansion Pack v1
**Priority:** High

---

## Overview

Build the **TitanEdgeSync** engine — a robust offline-first execution and synchronisation system that allows field technicians to complete jobs, fill checklists, capture evidence, and record inspection responses without network connectivity, then sync back to the server when connection is restored.

TitanEdgeSync manages the full sync lifecycle: queue, conflict detection, merge strategy, rollback on failure, and acknowledgement. It integrates with the PWA layer (Service Worker), the existing `ServiceJob`, `ChecklistRun`, `InspectionInstance`, and `TrustWorkLedger` (Module 03), and emits Titan Signals for every sync event.

---

## Pre-Scan Requirement (MANDATORY — run before any implementation pass)

1. Read `app/Models/Work/ServiceJob.php` — all status fields, stage transitions
2. Read `app/Models/Work/ChecklistRun.php`, `ChecklistResponse.php` — offline completion data
3. Read `app/Models/Inspection/InspectionInstance.php`, `InspectionResponse.php` — offline inspection data
4. Read `app/Titan/Signals/SignalDispatcher.php` and `ProcessStateMachine.php`
5. Read `app/Extensions/TitanRewind/System/` — at least `RewindEngine.php` and `RewindConflictDetector.php` — understand conflict resolution patterns
6. Read `database/migrations/` — all job, checklist, inspection table schemas
7. Read `docs/nexuscore/` — scan for offline, sync, edge, PWA, or mobile design docs
8. Read `docs/titancore/` — scan for edge intelligence and PWA architecture docs
9. Read `CodeToUse/mobile_app_backend/` — scan ALL files for sync patterns, queue structures, or conflict resolution entities
10. Read `app/Models/Concerns/BelongsToCompany.php` — multi-tenant scoping pattern

---

## Canonical Models to Extend / Reference

- `app/Models/Work/ServiceJob.php`
- `app/Models/Work/ChecklistRun.php`
- `app/Models/Inspection/InspectionInstance.php`
- `app/Extensions/TitanRewind/System/RewindConflictDetector.php`
- `app/Extensions/TitanRewind/System/RewindRollbackProcessor.php`

---

## Required Output Artifacts

### Migrations
- `database/migrations/YYYY_MM_DD_create_edge_sync_tables.php`
  - `edge_sync_queues` — client-side operations waiting to sync: `id`, `company_id`, `device_id` (string), `user_id`, `operation_type` (job_update|checklist_response|inspection_response|evidence_upload|signature_capture|job_complete), `subject_type`, `subject_id`, `payload` (json — full operation data), `client_created_at` (datetime — client timestamp), `status` (pending|processing|synced|conflict|failed), `attempts`, `last_attempt_at`, `error_message`, `created_at`, `updated_at`
  - `edge_sync_conflicts` — merge conflicts: `sync_queue_id`, `conflict_type` (field_collision|version_mismatch|deleted_subject|concurrent_edit), `server_state` (json), `client_state` (json), `resolved_by` (user|system|ai), `resolution` (json nullable), `resolved_at`
  - `edge_device_sessions` — device registration: `company_id`, `user_id`, `device_id` (string unique per user), `device_name`, `platform` (ios|android|web|pwa), `last_sync_at`, `sync_cursor` (last synced event id), `is_active`
  - `edge_sync_log` — completed sync audit: `company_id`, `user_id`, `device_id`, `batch_id` (uuid), `operations_count`, `conflicts_count`, `resolved_count`, `failed_count`, `started_at`, `completed_at`
- Use `Schema::hasTable()` / `Schema::hasColumn()` guards

### Models
- `app/Models/Sync/EdgeSyncQueue.php` — with `BelongsToCompany`, status scopes
- `app/Models/Sync/EdgeSyncConflict.php` — with `BelongsTo(EdgeSyncQueue)`
- `app/Models/Sync/EdgeDeviceSession.php` — device session tracking
- `app/Models/Sync/EdgeSyncLog.php` — sync batch audit

### Services
- `app/Services/Sync/EdgeSyncService.php`
  - `registerDevice(User $user, array $deviceData): EdgeDeviceSession`
  - `ingestBatch(User $user, string $deviceId, array $operations): array` — returns sync results
  - `processOperation(EdgeSyncQueue $item): bool`
  - `detectConflicts(EdgeSyncQueue $item): ?EdgeSyncConflict`
  - `resolveConflict(EdgeSyncConflict $conflict, string $strategy): void` — strategies: server_wins|client_wins|merge|manual
  - `getDeltaForDevice(EdgeDeviceSession $session): array` — server changes since last sync cursor
  - `acknowledgeBatch(string $batchId, string $deviceId): void`
- `app/Services/Sync/EdgeConflictResolverService.php`
  - `autoResolve(EdgeSyncConflict $conflict): bool`
  - `buildMergeStrategy(EdgeSyncConflict $conflict): string`
  - `applyResolution(EdgeSyncConflict $conflict, array $resolution): void`
- `app/Services/Sync/EdgeSyncPayloadProcessor.php`
  - `applyJobUpdate(array $payload): ServiceJob`
  - `applyChecklistResponse(array $payload): ChecklistResponse`
  - `applyInspectionResponse(array $payload): InspectionResponse`
  - `applySignatureCapture(array $payload): TrustEvidenceAttachment` — integrates with Module 03
  - `applyJobCompletion(array $payload): ServiceJob`

### Events
- `app/Events/Sync/EdgeBatchSynced.php`
- `app/Events/Sync/EdgeConflictDetected.php`
- `app/Events/Sync/EdgeConflictResolved.php`
- `app/Events/Sync/EdgeSyncFailed.php`

### Listeners
- `app/Listeners/Sync/RecordSyncEventOnTrustLedger.php` — integrates with Module 03

### Signals
- Emit via `SignalDispatcher`: `sync.batch_received`, `sync.conflict_detected`, `sync.conflict_resolved`, `sync.batch_complete`
- Include `device_id`, `user_id`, `batch_id`, `operations_count` in signal context

### API Controllers / Routes
- `app/Http/Controllers/Api/Sync/EdgeSyncController.php` (REST API — not web)
  - `POST /api/sync/register` — register device
  - `POST /api/sync/push` — push batch of operations from device
  - `GET  /api/sync/pull` — get delta of server changes since cursor
  - `POST /api/sync/acknowledge` — acknowledge batch receipt
  - `GET  /api/sync/conflicts` — list unresolved conflicts for device
  - `POST /api/sync/conflicts/{id}/resolve` — resolve a specific conflict
- Register in `routes/api.php` under `sync` prefix with auth middleware

### Tests
- `tests/Unit/Services/Sync/EdgeSyncServiceTest.php`
- `tests/Unit/Services/Sync/EdgeConflictResolverServiceTest.php`
- `tests/Feature/Api/Sync/EdgeSyncControllerTest.php`

### Docs Report
- `docs/modules/MODULE_05_TitanEdgeSync_report.md` — sync protocol spec, conflict resolution strategy table, payload format per operation type, cursor model explanation

### FSM Update
- Update `fsm_module_status.json` — set `titan_edge_sync` to `installed`

---

## Architecture Notes

- Operations in a batch MUST be processed in `client_created_at` order — timestamp ordering is critical
- `device_id` is client-generated UUID — server must not trust it as unique across companies (scope by `user_id + device_id`)
- Conflict detection: compare `client_created_at` against `updated_at` of the server record — if server was modified after `client_created_at`, flag as conflict
- Default resolution strategy: `server_wins` for metadata fields, `merge` for checklist responses (each item is independent), `manual` for job status
- `getDeltaForDevice()` uses `sync_cursor` (last processed event ID) — returns all events after that cursor
- Must integrate with `TrustWorkLedger` (Module 03) — any job completion or signature synced offline must create a ledger entry
- API endpoints must be stateless and token-authenticated — use Laravel Sanctum token auth
- Payload format must be versioned (`version` field in each operation) for forward compatibility
- Respect `company_id` scoping — `device_id` operations must be validated against authenticated user's company

---

## References

- `app/Models/Work/ServiceJob.php`
- `app/Models/Work/ChecklistRun.php`, `ChecklistResponse.php`
- `app/Models/Inspection/InspectionInstance.php`, `InspectionResponse.php`
- `app/Extensions/TitanRewind/System/RewindConflictDetector.php`
- `app/Extensions/TitanRewind/System/RewindRollbackProcessor.php`
- `app/Titan/Signals/SignalDispatcher.php`
- `app/Titan/Signals/ProcessStateMachine.php`
- `app/Services/Trust/TrustLedgerService.php` (Module 03 output)
- `CodeToUse/mobile_app_backend/` (full scan)
- `docs/nexuscore/` (offline, edge, PWA docs)
- `docs/titancore/` (edge intelligence architecture)
