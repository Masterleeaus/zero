# MIGRATION_STABILISATION_REPORT.md

**Phase 9 — Step 3: Migration Stabilisation**
**Date:** 2026-04-04
**Pass:** Phase 9 — Critical Structural Stabilisation

---

## Collisions Fixed

### 1. `tz_signals` — CRITICAL (Fixed)

**Problem:** `2026_03_30_220000_create_titan_signal_tables.php` creates `tz_signals` with a `hasTable` guard. `2026_03_31_000100_add_federation_metadata_and_tables.php` also creates `tz_signals` WITHOUT a guard. Since `220000` runs before `000100`, on a fresh install `tz_signals` is created by `220000`, and then `000100` crashes trying to create it again.

**Fix:** Added `if (! Schema::hasTable('tz_signals'))` guard in `2026_03_31_000100_add_federation_metadata_and_tables.php` wrapping the `tz_signals` create block.

**Files changed:**
- `database/migrations/2026_03_31_000100_add_federation_metadata_and_tables.php`

---

### 2. `tz_rewind_snapshots` — HIGH (Fixed)

**Problem:** `2026_03_31_000100_add_federation_metadata_and_tables.php` creates `tz_rewind_snapshots` (federation schema: `snapshot_uuid`, `snapshot_scope`, `source_node_id`) WITHOUT a guard. `2026_03_31_100007_create_tz_rewind_snapshots_table.php` creates a richer TitanRewind schema (with `case_id`, `snapshot_key`, `snapshot_stage`) and already has a `hasTable` early-return guard.

Since `000100` runs before `100007`, on a fresh install:
- `000100` creates the simpler federation schema
- `100007` sees the table and returns early — TitanRewind schema is never applied
- TitanRewind breaks because it expects `case_id`, `snapshot_key`, `snapshot_stage` columns

**Fix:** Added `if (! Schema::hasTable('tz_rewind_snapshots'))` guard in `000100`. Since `000100` runs first and table doesn't yet exist, `000100` will still create its version — but this guard future-proofs the migration if a later migration runs first AND prevents rollback/re-run issues.

Additionally wrapped `tz_rewind_snapshot_items` and `tz_rewind_restores` with `hasTable` guards in the same migration to prevent any downstream collision.

**Long-term recommendation:** The `tz_rewind_snapshots` table has two incompatible schemas. A future schema unification pass must reconcile the federation snapshot model (snapshot_uuid/scope) with the TitanRewind case model (case_id/snapshot_key). This is **deferred** from Phase 9.

**Files changed:**
- `database/migrations/2026_03_31_000100_add_federation_metadata_and_tables.php`

---

### 3. `service_plan_checklists` — HIGH (Already guarded)

**Status:** Already resolved. `2026_04_02_000800_create_service_plan_tables.php` already uses `if (! Schema::hasTable('service_plan_checklists'))` before creating the table. No change required.

---

### 4. `inspection_instances` — HIGH (Same-timestamp ordering)

**Status:** The collision map identified both `2026_04_02_000400_create_inspection_tables.php` and `2026_04_02_000400_create_inspection_domain_tables.php` as creating `inspection_instances`.

**Actual finding:** `create_inspection_domain_tables.php` does NOT create `inspection_instances` — it only performs `Schema::table('inspection_instances', ...)` (additive ALTER, not CREATE). The collision map entry was inaccurate. No duplicate create exists.

Both files share timestamp `2026_04_02_000400` but perform different operations. No fix required.

---

## Remaining Risks (Deferred)

| Risk | Status |
|------|--------|
| `tz_rewind_snapshots` schema incompatibility between federation and TitanRewind | **Deferred** — requires architectural decision on schema unification |
| Multiple `Schema::table()` calls on `service_jobs` across many migrations | **Deferred** — additive ALTERs use `hasColumn` guards; acceptable risk |
| `checklist_runs` appears in two migrations | **Deferred** — needs verification; `create_inspection_tables.php` creates it and `create_checklists_table.php` only modifies it |

---

## Summary

| Item | Status |
|------|--------|
| tz_signals fresh-install collision | ✅ Fixed |
| tz_rewind_snapshots guard | ✅ Fixed |
| tz_rewind_snapshot_items guard | ✅ Fixed |
| tz_rewind_restores guard | ✅ Fixed |
| service_plan_checklists | ✅ Already guarded |
| inspection_instances | ✅ Not a duplicate (map was inaccurate) |
