# WorkCore → MagicAI pre-merge prep

Prepared from the uploaded WorkCore ZIP using the uploaded Laravel pre-merge extraction guide.

## What this package does
- removes obvious standalone Laravel infrastructure duplicates
- strips auth/bootstrap/install artifacts that MagicAI should own
- keeps feature/domain code for later namespace + route + migration refactor
- adds merge notes and audit scripts

## Current prepared package stats
- PHP files: 3421
- Total files: 4627
- Removed paths: 100

## Important
This is a **pre-merge prep package**, not a fully namespaced MagicAI-ready drop-in. The next pass should:
1. move kept domains under explicit namespaces/modules
2. split remaining routes into feature route files
3. rename surviving migrations with workcore/titan prefixes
4. bind retained services through one consolidation provider
5. map user/company/team relationships onto MagicAI host models


## Pass 3 update
- Removed more generic host-owned runtime/test/config artifacts: 36 items
- Remaining PHP files: 3294
- Remaining total files: 4348
- Added retained-config, controller-domain, and migration-bucket inventories


## Pass 4 update
- Removed source kernel/provider plus seeders, factories, and schema dump
- Remaining PHP files: 3240
- Remaining total files: 4302
- Added route-slice, command-inventory, provider-snippet, and seeder-strategy notes


## Pass 5
- Stripped compiled/public/runtime layers to keep this package feature-first for MagicAI merge.
- Preserved source assets in resources/, app/, routes/, database/, and config/ for integration.
- Added model, service, and view inventories plus public asset strip map.


## Pass 7
- Removed repository dependency manifests/docs that the MagicAI host should own.
- Added per-domain file manifests and a slice extraction script for the next actual merge passes.
- crm_leads_customers: 330 files classified.
- sites_service jobs_time: 404 files classified.
- finance_sales: 391 files classified.
- hr_attendance_leave: 362 files classified.
- support_comms: 433 files classified.
- platform_misc: 1059 files classified.
