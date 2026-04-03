# DOC 53 — Copilot Prompt 03: Route Canonicalizer

## Objective
Rename and reorganize routes into canonical Nexus five-mode naming.

## Inputs
- mode_ownership_registry.md
- entity_lock_table.md

## Route Targets
- titan.jobs.*
- titan.comms.*
- titan.finance.*
- titan.admin.*
- titan.social.*

## Tasks
1. scan all route definitions
2. classify each route by primary mode
3. rewrite route names to canonical mode namespace
4. preserve middleware and access logic
5. rewrite internal references to renamed route names
6. generate a complete old->new route mapping table

## Critical Rules
1. Preserve behavior.
2. Do not delete route functionality unless duplicate and proven obsolete.
3. Do not collapse Social mode into other modes.
4. Preserve compatibility aliases only if explicitly documented.
5. Output complete mapping report.

## Required Outputs
1. route_mapping_table.md
2. compatibility_aliases.md
3. unresolved_route_conflicts.md
