# MIGRATION_COLLISION_MAP.md

**Phase 8 — Step 3: Migration Namespace Collision Audit**
**Date:** 2026-04-03
**Scope:** database/migrations, CodeToUse migration files

---

## 1. Confirmed Duplicate Table Creation in Core Migrations

The following tables are created by more than one migration file in `database/migrations/`:

### 1a. `tz_signals` — CRITICAL

| Migration File | Line | Action |
|---------------|------|--------|
| `2026_03_30_220000_create_titan_signal_tables.php` | 43 | `Schema::create('tz_signals', ...)` |
| `2026_03_31_000100_add_federation_metadata_and_tables.php` | 272 | `Schema::create('tz_signals', ...)` |

**Impact:** Running both migrations in sequence will throw a "table already exists" error. The second migration must be guarded or the table creation removed from `000100`.

### 1b. `tz_rewind_snapshots` — CRITICAL

| Migration File | Line | Action |
|---------------|------|--------|
| `2026_03_31_100007_create_tz_rewind_snapshots_table.php` | 15 | `Schema::create('tz_rewind_snapshots', ...)` |
| `2026_03_31_000100_add_federation_metadata_and_tables.php` | 315 | `Schema::create('tz_rewind_snapshots', ...)` |

**Impact:** Same as above — two create statements for the same table. The `000100` federation migration appears to bundle multiple table creations that are also defined in their own dedicated migration files.

### 1c. `inspection_instances` — HIGH

| Migration File | Action |
|---------------|--------|
| `2026_04_02_000400_create_inspection_tables.php` | `Schema::create('inspection_instances', ...)` |
| `2026_04_02_000400_create_inspection_domain_tables.php` | `Schema::create('inspection_instances', ...)` |

**Note:** Both files share the **same timestamp prefix** `2026_04_02_000400`. This is an ordering risk — Laravel runs migrations alphabetically when timestamps match. Both attempt to create `inspection_instances`.

### 1d. `checklist_runs` — HIGH

| Migration File | Action |
|---------------|--------|
| `2026_04_02_000400_create_inspection_tables.php` | `Schema::create('checklist_runs', ...)` |
| (Also referenced in `2026_03_30_075300_create_checklists_table.php`) | Table touched |

### 1e. `service_plans` + `service_plan_visits` + `service_plan_checklists` — HIGH

| Migration File | Action |
|---------------|--------|
| `2026_04_02_000100_create_service_plan_tables.php` | Creates `service_plans`, `service_plan_visits`, `service_plan_checklists` |
| `2026_04_02_000800_create_service_plan_tables.php` | Also creates `service_plan_checklists` |

**Note:** Two migration files are named `create_service_plan_tables.php` with different timestamps (000100 vs 000800). Both attempt to create `service_plan_checklists`.

### 1f. `site_assets` + `asset_service_events` — HIGH

| Migration File | Action |
|---------------|--------|
| `2026_04_02_000300_create_site_assets_table.php` | Creates `site_assets`, `asset_service_events` |
| `2026_04_02_000600_create_site_asset_tables.php` | Also creates `asset_service_events` |

---

## 2. Tables with Multiple ALTER/Schema::table References (Re-definition Risk)

The following tables appear in `Schema::table()` calls across many migrations — each call may add conflicting columns or indexes:

| Table | Migrations Touching It | Risk |
|-------|----------------------|------|
| `service_jobs` | 210100, 000300/400/500, 100000, 400000, 500100/200/300 | HIGH — many column additions, risk of duplicate column |
| `service_agreements` | 400000, 500100, 500200 | MEDIUM |
| `service_plan_visits` | 000100, 500200 | MEDIUM |
| `sites` | 200100, 200400 | MEDIUM |
| `quote_items` | 500100 | LOW |
| `quotes` | 500100 | LOW |
| `job_stages` | 500300 | LOW |
| `users` | Multiple from 2023–2026 | LOW (standard pattern) |
| `settings` | Multiple from 2023–2026 | LOW (standard pattern) |

---

## 3. Timestamp Ordering Risks (Same-Timestamp Migrations)

Laravel runs migrations in timestamp+filename alphabetical order. The following pairs share the same timestamp:

| Timestamp | File A | File B | Risk |
|-----------|--------|--------|------|
| `2026_04_02_000400` | `create_inspection_tables.php` | `create_inspection_domain_tables.php` | **CRITICAL** — both create `inspection_instances` |
| `2026_03_31_200000` | `create_service_area_regions_table.php` | `create_territory_hierarchy_tables.php` | MEDIUM — may overlap on territory-related tables |

---

## 4. CodeToUse Migration Files (Not Yet Active)

CodeToUse domains contain their own migration files. These are NOT in `database/migrations/` and are not auto-discovered. However, future integration may activate them.

### 4a. Voice Extension Migrations (Multiple Copies)

The following migration files exist in **multiple Voice passes** (Pass1, Pass2, Pass3, Pass8, Pass11, Unified, MagicAI):

| Migration Name | Present In |
|---------------|-----------|
| `2025_05_23_143630_create_voice_chat_bots_table.php` | 7+ Voice passes |
| `2025_05_23_154408_create_voice_chat_bot_trains_table.php` | 7+ Voice passes |
| `2026_04_02_000000_add_voice_command_tables.php` | 7+ Voice passes |
| `2026_04_03_000000_add_phone_bot_tables.php` | 7+ Voice passes |
| `2026_04_04_000000_add_unified_voice_os_tables.php` | 7+ Voice passes |
| `2025_04_25_130930_create_ext_voice_chatbots_table.php` | 7+ Voice passes |
| `2025_04_29_073630_create_ext_voicechatbot_histories_table.php` | 7+ Voice passes |

**Note:** Running all these migrations would create tables multiple times. Only ONE voice pass should be chosen.

### 4b. CheckoutRegistration Extension Migration

| Migration | Risk |
|-----------|------|
| `2025_01_15_170129_add_reg_sub_status_column_to_users_table.php` | Adds column to `users` — may conflict with existing `users` table structure |

### 4c. LiveCustomizer Migration

| Migration | Risk |
|-----------|------|
| `2024_05_08_163635_menu_migrate.php` | May alter core `menus` table — collision risk with `App\Models\Common\Menu` |

### 4d. AiChatProFolders Extension

| Migration | Risk |
|-----------|------|
| `2026_01_05_163811_add_folder_id_column_to_user_openai_table.php` | Adds `folder_id` to `user_openai` — may conflict if another pass already added this column |
| `2025_12_26_163811_add_chatpro_folders_table.php` | Creates new table |

---

## 5. Migration Class Name Conflicts

No duplicate PHP class names detected within `database/migrations/` (all are anonymous classes extending `Migration`). The `class extends` output from the duplicate class check confirms this is anonymous class pattern — **no named class duplicates**.

---

## 6. Summary Table

| Risk Level | Finding |
|------------|---------|
| **CRITICAL** | `tz_signals` created in 2 migrations — will fail on fresh install |
| **CRITICAL** | `tz_rewind_snapshots` created in 2 migrations — will fail on fresh install |
| **CRITICAL** | `inspection_instances` created in 2 same-timestamp migrations |
| **HIGH** | `service_plan_checklists` created in both 000100 and 000800 |
| **HIGH** | `asset_service_events` created in both 000300 and 000600 |
| **HIGH** | 7+ identical Voice migration sets in CodeToUse — will collide if more than one activated |
| **MEDIUM** | Same-timestamp territory migration files may have ordering issues |
| **MEDIUM** | Many migrations alter `service_jobs` — duplicate column risk in high-activity table |
| **LOW** | `checklist_runs` touched in multiple migrations |
| **LOW** | CodeToUse extension migrations are inactive but would conflict when activated |
