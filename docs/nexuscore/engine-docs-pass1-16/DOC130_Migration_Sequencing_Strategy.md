# Migration Sequencing Strategy

## Objective
Ensure safe schema transition to tz_* namespace without data loss.

## Order
1. Snapshot current schema
2. Register rename mapping table
3. Create shadow tz_* tables
4. Backfill data
5. Verify integrity checks
6. Switch read layer
7. Switch write layer
8. Archive legacy tables

## Outputs
schema_snapshot_manifest.md
rename_mapping_registry.md
integrity_check_report.md
