# [spark] Field Checklist Runner Panel

## Docs to read first
- Nexus bundle: DOC80_Jobs_Mode_Blueprint.md
- 09_MOBILE_APPS_AND_CLIENT_SURFACES/25_MOBILE_SURFACE_CAPABILITY_MATRIX.md
- 09_MOBILE_APPS_AND_CLIENT_SURFACES/MOBILE_STACK_ALIGNMENT.md
- 05_DATA_SCHEMA_AND_TABLES/DOC121_Node_vs_PWA_Data_Boundary.md
- 09_MOBILE_APPS_AND_CLIENT_SURFACES/26_CHATBOT_BUILDER_TO_PWA_BRIDGE.md

Purpose
Build the mobile-first checklist execution panel for field workers, including completion, notes, issue flags, and proof capture hooks.

Why this app is priority
It is one of the highest-value operational panels in the system and directly impacts service quality.

Mobile-first UX requirements
- Designed primarily for phone use in the field
- One task per row/card with obvious completion state
- Fast note and issue capture
- Camera/proof hooks visible and easy to use

Chat-launch / wizard-launch behavior
- Launch from Today Jobs Panel, Job Setup flow, or chat intents like “open today’s checklist”
- Can return control to Completion-to-Invoice wizard when finished

Data boundaries and persistence rules
- Checklist item truth must persist to core structures
- Temporary offline queue/cache is allowed but must sync back to the source of truth
- Follow DOC121 boundaries strictly

Docs to read first
- DOC80_Jobs_Mode_Blueprint.md
- 25_MOBILE_SURFACE_CAPABILITY_MATRIX.md
- MOBILE_STACK_ALIGNMENT.md
- DOC121_Node_vs_PWA_Data_Boundary.md
- 26_CHATBOT_BUILDER_TO_PWA_BRIDGE.md

Drift reconciliation note
Keep the app in Work Hub / field-worker language; do not drift back into generic task terminology if job/checklist concepts are already defined.

Scope included
- checklist items
- completion toggles
- notes
- issue flagging
- proof/photo action hooks
- progress summary

Scope excluded
- full route management
- billing logic
- CRM editing

Acceptance criteria
- Mobile-first field panel works well on phones
- Can complete items, add notes, and flag issues
- Supports proof capture hooks and completion summaries
- Can hand off to next lifecycle step

Suggested component breakdown
- ChecklistHeader
- ChecklistItemCard
- NotesDrawer
- IssueFlagModal
- ProofActionBar
- ProgressFooter

Suggested API/data dependencies
- checklist items endpoint
- checklist update endpoint
- proof attachment endpoint or stub
- issue creation endpoint or stub

Output expectation for Spark
Produce a standalone React mini-app optimized for field execution with PWA-friendly assumptions and composable lifecycle handoffs.
