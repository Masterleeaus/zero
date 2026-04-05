# Meta Prompt — Create 10 GitHub Issues for Nexus Spark Mini-Apps

You are creating GitHub issues for the Nexus Spark mini-app program.

## Goal
Create exactly **10 GitHub issues**, one issue per mini-app, each with **`[spark]`** in the title.

## Critical instruction
Before creating any issue, deep-read the latest docs pack and the nested Nexus docs bundle. Do **not** rely on old assumptions. Reconcile the docs against the **current canonical product direction** below.

## Current canonical product direction
- Omni owns all comms and channel surfaces.
- Nexus no longer owns comms.
- Nexus is now a 4-hub operational system:
  - Work
  - Money
  - Office
  - Grow
- Nexus main entry is chat-first.
- Nexus Home = primary chat interface + 5 cards:
  - Work
  - Money
  - Office
  - Grow
  - Omni
- Users should eventually be able to operate mostly from chat.
- Spark is being used to generate **mini React apps / mobile-first PWA-friendly surfaces**.
- Current Nexus priorities are:
  1. Scout configuration screens
  2. Lifecycle wizard UIs
  3. Field-worker mobile panels

## Docs reading requirement
Read these docs first from the latest docs zip:

### Top-level docs
- `TitanZero_Docs_Cleaned_v10/09_MOBILE_APPS_AND_CLIENT_SURFACES/25_MOBILE_SURFACE_CAPABILITY_MATRIX.md`
- `TitanZero_Docs_Cleaned_v10/09_MOBILE_APPS_AND_CLIENT_SURFACES/MOBILE_STACK_ALIGNMENT.md`
- `TitanZero_Docs_Cleaned_v10/09_MOBILE_APPS_AND_CLIENT_SURFACES/26_CHATBOT_BUILDER_TO_PWA_BRIDGE.md`
- `TitanZero_Docs_Cleaned_v10/04_FINANCE_AND_MONEY/DOC82_Finance_Mode_Blueprint.md`
- `TitanZero_Docs_Cleaned_v10/05_DATA_SCHEMA_AND_TABLES/DOC121_Node_vs_PWA_Data_Boundary.md`
- `TitanZero_Docs_Cleaned_v10/05_DATA_SCHEMA_AND_TABLES/DOC122_Mode_Ownership_Table_Map.md`
- `TitanZero_Docs_Cleaned_v10/05_DATA_SCHEMA_AND_TABLES/48_LIFECYCLE_STATE_TABLE_SCHEMA.md`

### Embedded Nexus docs bundle
Unpack and read:
`TitanZero_Docs_Cleaned_v10/07_INTEGRATION_MIGRATION_AND_CONSOLIDATION/Nexus_Engine_Docs_Pass1_2_3_4_5_6_7_8_9_10_11_12_13_14_15_16.zip`

Inside that bundle, read at minimum:
- `README.md`
- `DOC12_Scout_Layer.md`
- `DOC18_View_Surface_Map.md`
- `DOC22_Lifecycle_State_Machine.md`
- `DOC23_Mode_Lifecycle_Overlays.md`
- `DOC38_Master_Development_Roadmap.md`
- `DOC80_Jobs_Mode_Blueprint.md`
- `DOC82_Finance_Mode_Blueprint.md`
- `DOC83_Admin_Mode_Blueprint.md`
- `DOC121_Node_vs_PWA_Data_Boundary.md`
- `DOC145_Final_Prebuild_Checklist.md`

## Drift reconciliation rule
The docs still contain older five-mode Nexus language, including Comms and Social Media inside Nexus.
Do **not** reproduce that older model blindly.
Instead:
- treat those older docs as source material
- map Comms responsibilities to Omni
- map Social/Growth concepts into Grow Hub where appropriate
- preserve useful lifecycle, scout, signal, governance, and table-boundary concepts
- write a short **Drift Reconciliation Note** in each issue explaining how the issue aligns old docs to the current architecture

## Before creating the 10 issues
Add one short section called:
`Gaps / assumptions found in docs`
Include any missing details such as:
- no canonical 4-hub Nexus doc
- no explicit top-10 Spark mini-app list
- no exact route/data contract for each mini-app
- no dedicated Scout UI spec
- no dedicated lifecycle wizard UI spec
- no dedicated field-worker panel UI spec

## Issue creation rules
Create exactly 10 issues.
Each title must begin with:
`[spark]`

Each issue must contain these sections:
1. Purpose
2. Why this app is priority
3. Mobile-first UX requirements
4. Chat-launch / wizard-launch behavior
5. Data boundaries and persistence rules
6. Docs to read first
7. Drift reconciliation note
8. Scope included
9. Scope excluded
10. Acceptance criteria
11. Suggested component breakdown
12. Suggested API/data dependencies
13. Output expectation for Spark

## Important implementation rules
- These are mini React app issues for Spark.
- Design mobile-first.
- Prefer PWA-friendly assumptions.
- Respect Node vs PWA boundary:
  - no operational truth stored in PWA runtime tables
- Keep operational truth in core tables / lifecycle structures.
- Every app should be launchable from Nexus chat or hub cards.
- Every app should be composable into the larger Nexus shell later.
- Do not make giant monolith issue scopes.
- Keep each issue narrow enough for one mini-app but complete enough to be buildable.

## Create these 10 issues
1. [spark] Nexus Home Command Surface
2. [spark] Scout Mode Configurator
3. [spark] Scout Lifecycle Mapping Editor
4. [spark] Scout Signal Rules Engine
5. [spark] Lead-to-Job Lifecycle Wizard
6. [spark] Job Setup Lifecycle Wizard
7. [spark] Completion-to-Invoice Lifecycle Wizard
8. [spark] Work Hub Today Jobs Panel
9. [spark] Field Checklist Runner Panel
10. [spark] Site Memory Panel

## Required final output
Return:
A. Short audit summary of what docs were used
B. Gaps / assumptions found in docs
C. The 10 fully written GitHub issue drafts

Do not stop after analysis. Produce all 10 issue drafts in one pass.
