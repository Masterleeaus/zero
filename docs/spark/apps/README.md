# Nexus Spark — Top 10 Mini-App Issue Pack

> Source: `docs/spark/Titan_Nexus_Spark_Top10_Issue_Pack_v1.zip`

This folder contains 10 GitHub issue drafts for the **Nexus Spark mini-app programme**.
Each file is ready to be created as a GitHub issue with the title and body below.

---

## Gaps / Assumptions found in docs

1. No single canonical doc that fully states **Omni owns comms** and **Nexus = Work / Money / Office / Grow hubs**.
2. No single canonical doc defining **Nexus Home as chat-first launcher** with 5 cards.
3. No dedicated doc that formalises the **top 10 Nexus Spark mini-app priority list**.
4. No dedicated doc consolidating **Scout configuration UI requirements**.
5. No dedicated doc consolidating **Lifecycle wizard UI requirements**.
6. No dedicated doc consolidating **Field-worker mobile panel requirements**.
7. No per-mini-app route/data/API contract spec.
8. No standard GitHub issue template for **Spark mini React app** creation.

### Working assumptions applied to all 10 issues
- Old Jobs Mode → **Work Hub**
- Old Finance Mode → **Money Hub**
- Old Admin Mode → **Office Hub**
- Old Social / Growth → **Grow Hub**
- Old Comms Mode → **Omni** (not Nexus)
- Spark outputs = standalone mini React apps, composable into future Nexus shell
- Mobile-first and PWA-friendly assumptions are explicit in every issue
- `DOC121_Node_vs_PWA_Data_Boundary.md` enforced: PWA/mini-app state is not the source of operational truth

---

## Issue index

| # | File | GitHub issue title |
|---|------|--------------------|
| 1 | `01_spark_nexus_home_command_surface.md` | `[spark] Nexus Home Command Surface` |
| 2 | `02_spark_scout_mode_configurator.md` | `[spark] Scout Mode Configurator` |
| 3 | `03_spark_scout_lifecycle_mapping_editor.md` | `[spark] Scout Lifecycle Mapping Editor` |
| 4 | `04_spark_scout_signal_rules_engine.md` | `[spark] Scout Signal Rules Engine` |
| 5 | `05_spark_lead_to_job_lifecycle_wizard.md` | `[spark] Lead-to-Job Lifecycle Wizard` |
| 6 | `06_spark_job_setup_lifecycle_wizard.md` | `[spark] Job Setup Lifecycle Wizard` |
| 7 | `07_spark_completion_to_invoice_lifecycle_wizard.md` | `[spark] Completion-to-Invoice Lifecycle Wizard` |
| 8 | `08_spark_work_hub_today_jobs_panel.md` | `[spark] Work Hub Today Jobs Panel` |
| 9 | `09_spark_field_checklist_runner_panel.md` | `[spark] Field Checklist Runner Panel` |
| 10 | `10_spark_site_memory_panel.md` | `[spark] Site Memory Panel` |

---

## How to create the issues

Each file in this folder is a complete issue body.  
Use the file name minus the `.md` extension as guidance for the issue title (shown in the table above).

### Via GitHub UI
1. Go to **Issues → New issue** in the `Masterleeaus/zero` repository.
2. Paste the title from the table above.
3. Copy the file contents as the issue body.
4. Add labels: `spark`, plus any hub-specific labels shown at the bottom of each file.

### Via GitHub CLI (if auth is available)
```bash
gh issue create \
  --title "[spark] Nexus Home Command Surface" \
  --body-file docs/spark/apps/01_spark_nexus_home_command_surface.md \
  --label spark,nexus,mobile,PWA
```
