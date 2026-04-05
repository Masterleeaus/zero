# [spark] Scout Lifecycle Mapping Editor

## Docs to read first
- Nexus bundle: DOC22_Lifecycle_State_Machine.md
- Nexus bundle: DOC23_Mode_Lifecycle_Overlays.md
- 05_DATA_SCHEMA_AND_TABLES/48_LIFECYCLE_STATE_TABLE_SCHEMA.md
- 05_DATA_SCHEMA_AND_TABLES/DOC121_Node_vs_PWA_Data_Boundary.md
- 05_DATA_SCHEMA_AND_TABLES/DOC122_Mode_Ownership_Table_Map.md

Purpose
Build the UI for editing lifecycle mappings such as lead → quote → booking → job → completion → invoice → review.

Why this app is priority
The lifecycle map is the backbone that links hubs together.

Mobile-first UX requirements
- Vertical, swipe-light step editor
- Thumb-friendly state cards
- Add/reorder/remove state controls without desktop drag dependency
- Clear transition summaries

Chat-launch / wizard-launch behavior
- Launchable from chat intents like “change the flow after completion” or “map quote to invoice”
- Wizard surfaces should deep-link back to the lifecycle step they belong to

Data boundaries and persistence rules
- UI edits documented lifecycle structures only
- Respect PWA/node boundary: lifecycle truth remains in core tables, not browser cache
- Keep transitions traceable to lifecycle schema docs

Docs to read first
- DOC22_Lifecycle_State_Machine.md
- DOC23_Mode_Lifecycle_Overlays.md
- 48_LIFECYCLE_STATE_TABLE_SCHEMA.md
- DOC121_Node_vs_PWA_Data_Boundary.md
- DOC122_Mode_Ownership_Table_Map.md

Drift reconciliation note
Older docs may treat Comms as a mode transition inside Nexus. Re-route those transitions toward Omni handoff points where applicable.

Scope included
- lifecycle step list
- transition editor
- state metadata editor
- validation rules UI
- preview flow summary

Scope excluded
- Full workflow execution engine
- Omni channel-specific flows
- dispatch screens

Acceptance criteria
- Can view and edit ordered lifecycle states
- Can attach transition metadata to states
- Reflects current hub model and Omni handoffs
- Mobile-first interaction works without desktop-only gestures

Suggested component breakdown
- LifecycleStepList
- StepCard
- TransitionEditor
- StateMetaPanel
- ValidationSummary

Suggested API/data dependencies
- lifecycle states endpoint
- transition rules endpoint
- ownership/hub mapping data

Output expectation for Spark
Produce a standalone React mini-app for lifecycle editing with mobile-first layout and contract-based persistence assumptions.
