## WorkCore Federated DB Plan

### Layering principle
- **Business/domain tables stay readable and unprefixed** (customers, sites, service_jobs, checklists, service_issues, quotes, invoices, payments, workforce, scheduling).  
- **Titan federation / orchestration / sync tables use the `tz_*` prefix only**. This keeps “what the business is” separate from “how Titan syncs it”.
- Tenant boundary remains `company_id`; `team_id` stays for crew grouping (not tenancy). `user_id` continues to identify actors.

### Domain tables updated with federation metadata
Added lightweight sync/audit columns (uuid, origin/created/updated node ids, content_hash, schema_version, sync_status, last_local_modified_at, last_synced_at, tombstoned_at, visibility_scope, encryption_scope) to major operational tables:
- customers
- sites
- service_jobs
- checklists
- quotes
- invoices
- payments
- attendances
- shifts
- timelogs
- leaves

Child/detail tables got a slimmer set (uuid, origin/updated node ids, hashes, sync_status, last_synced_at, tombstoned_at, parent_object_uuid):
- quote_items
- invoice_items
- user_support
- user_support_messages

### New federation (`tz_*`) tables
Created Titan infra tables for nodes, keys, pairings, object registry, change log, sync inbox/outbox, conflicts, tombstones, sync sessions, signals + deliveries + subscriptions, and rewind snapshots/restores:
- tz_nodes, tz_node_keys, tz_node_pairings
- tz_object_registry, tz_change_log
- tz_sync_outbox, tz_sync_inbox, tz_sync_conflicts, tz_sync_sessions
- tz_tombstones
- tz_signals, tz_signal_deliveries, tz_signal_subscriptions
- tz_rewind_snapshots, tz_rewind_snapshot_items, tz_rewind_restores

### Quote alignment note
- Estimates are consolidated into the existing quote domain: estimate → quote, including request/items/templates mappings (see WORKCORE_RENAME_MAP.md).

### Rollout notes
- Migration is additive and forward-safe; existing integer IDs are preserved.
- New columns are nullable to avoid breaking current logic; models can start populating them progressively.
- Indexes added on uuid/object/sync-related columns to support sync and lookup performance.
- When ingesting legacy WorkCore data, map any estimate/project/task/ticket vocabulary to the aligned quote/service_job/checklist/service_issue terms before sync enablement.

### Compatibility
- Shared platform tables (roles, modules, settings, global_* etc.) are untouched and remain outside the Titan federation layer.
- team_id is preserved on existing tables; no changes to tenancy enforcement semantics.
