## WorkCore Federation Table Map

| Table | Type | New columns (if applicable) | Reason |
| --- | --- | --- | --- |
| customers | domain | uuid, origin_node_id, created_by_node_id, updated_by_node_id, content_hash, schema_version, sync_status, last_local_modified_at, last_synced_at, tombstoned_at, visibility_scope, encryption_scope | Stable cross-node identity and sync metadata |
| sites | domain | same as customers | Local-first site records with provenance |
| service_jobs | domain | same as customers | Job identity and sync state |
| checklists | domain | same as customers | Checklist identity and sync state |
| quotes | domain | same as customers | Quote identity; aligns estimate→quote |
| invoices | domain | same as customers | Invoice sync/audit |
| payments | domain | same as customers | Payment sync/audit |
| attendances | domain | same as customers | Workforce attendance sync |
| shifts | domain | same as customers | Shift scheduling sync |
| timelogs | domain | same as customers | Time tracking sync |
| leaves | domain | same as customers | Leave records sync |
| quote_items | child | uuid, origin_node_id, updated_by_node_id, content_hash, sync_status, last_synced_at, tombstoned_at, parent_object_uuid | Child item sync with parent linkage |
| invoice_items | child | same as quote_items | Child item sync with parent linkage |
| user_support | child | same as quote_items | Support/service issue threads (tickets) |
| user_support_messages | child | same as quote_items | Support message sync |
| tz_nodes | tz_federation | n/a | Node registry |
| tz_node_keys | tz_federation | n/a | Node key metadata |
| tz_node_pairings | tz_federation | n/a | Trusted pairings |
| tz_object_registry | tz_federation | n/a | Canonical object UUID registry |
| tz_change_log | tz_federation | n/a | Append-only change history |
| tz_sync_outbox | tz_federation | n/a | Outbound sync queue |
| tz_sync_inbox | tz_federation | n/a | Inbound staging/validation |
| tz_sync_conflicts | tz_federation | n/a | Conflict tracking |
| tz_tombstones | tz_federation | n/a | Deletion propagation |
| tz_sync_sessions | tz_federation | n/a | Sync run tracking |
| tz_signals | tz_federation | n/a | Signal/event bus |
| tz_signal_deliveries | tz_federation | n/a | Delivery tracking |
| tz_signal_subscriptions | tz_federation | n/a | Routing/subscriptions |
| tz_rewind_snapshots | tz_federation | n/a | Snapshot headers |
| tz_rewind_snapshot_items | tz_federation | n/a | Snapshot contents |
| tz_rewind_restores | tz_federation | n/a | Restore executions |

### Notes
- Domain/business tables stay unprefixed; federation/system tables exclusively use `tz_*`.
- Estimate vocabulary is consolidated into the existing quote domain (see WORKCORE_RENAME_MAP.md).
- Shared platform tables (roles, modules, settings, global_*, etc.) remain unchanged.
