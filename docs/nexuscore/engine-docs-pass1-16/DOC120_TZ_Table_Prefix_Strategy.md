# tz_* Table Prefix Strategy

## Objective
Standardize table prefixes across Nexus architecture.

## Core Prefixes
tz_core_*     → canonical business entities
tz_node_*     → device/node-local sync tables
tz_pwa_*      → UI/runtime shell support tables
tz_signal_*   → signal/event bus tables
tz_aegis_*    → governance checkpoints
tz_proc_*     → ProcessRecord lifecycle tracking

## Rules
- Business data always tz_core_*
- Device sync state always tz_node_*
- UI caches/runtime helpers tz_pwa_*
- No mixed semantic prefixes allowed
