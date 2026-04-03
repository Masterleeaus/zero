## WorkCore Sync & Rewind Notes

### Sync flow (local-first)
1. Domain tables gain UUID + node provenance + content hash + sync status to track local edits.
2. Changes append to `tz_change_log` and enqueue into `tz_sync_outbox` for delivery to other nodes/servers.
3. Incoming payloads land in `tz_sync_inbox`, are validated, then applied; conflicts are recorded in `tz_sync_conflicts`.
4. `tz_object_registry` tracks the latest known hash/nonce per object UUID for fast comparison.
5. `tz_sync_sessions` summarize each sync run (sent/received/conflicts/error summary).
6. `tz_tombstones` propagate deletes without dropping historical identity.

### Signal flow
- `tz_signals` capture orchestration events (with source object/node/team and payload).
- `tz_signal_subscriptions` define routing filters; `tz_signal_deliveries` record delivery attempts/status per subscriber/target.
- Signals are separate from business writes; they coordinate workflows and notifications across nodes.

### Conflict handling
- Detected divergences land in `tz_sync_conflicts` with local/remote hashes and change UUIDs.
- Resolution can be manual or automated; resolved_by/resolution_notes/resolved_at capture audit.

### Rewind model
- Snapshots: `tz_rewind_snapshots` (headers) + `tz_rewind_snapshot_items` (object entries with hashes/payload refs).
- Restores: `tz_rewind_restores` log execution of a rewind with scope, status, and timestamps.
- Works alongside tombstones to avoid resurrecting intentionally deleted objects.

### Local-first and server role
- Devices/nodes are primary writers; server coordinates signals, sync queues, conflict resolution, manifests, and audits.
- Domain tables stay business-readable; federation concerns live in `tz_*` tables.
- Tenancy: `company_id` boundary is preserved; `team_id` remains crew grouping only.
