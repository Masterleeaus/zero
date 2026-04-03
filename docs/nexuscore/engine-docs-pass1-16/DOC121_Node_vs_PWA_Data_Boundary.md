# Node vs PWA Data Boundary

## Node Tables (tz_node_*)
Purpose:
Offline sync + federated device coordination

Examples:
tz_node_devices
tz_node_sync_queue
tz_node_tombstones
tz_node_snapshots

## PWA Tables (tz_pwa_*)
Purpose:
Client runtime UX support only

Examples:
tz_pwa_sessions
tz_pwa_layout_state
tz_pwa_cache_registry

## Rule
Operational truth never stored in tz_pwa_*
