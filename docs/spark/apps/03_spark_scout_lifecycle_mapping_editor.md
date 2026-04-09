# [spark] Scout Lifecycle Mapping Editor

## 1. Purpose
Build the UI for editing lifecycle mappings — the ordered chain: lead → quote → booking → job → completion → invoice → review. Each state has metadata, transition conditions, and hub ownership.

## 2. Why this app is priority
The lifecycle map is the backbone that links hubs together. Every wizard and every hub panel depends on correct lifecycle mapping being maintained here.

## 3. Mobile-first UX requirements
- Vertical, swipe-light step editor
- Thumb-friendly state cards
- Add/reorder/remove state controls without desktop drag dependency
- Clear transition summaries

## 4. Chat-launch / wizard-launch behavior
- Launchable from chat intents like "change the flow after completion" or "map quote to invoice"
- Wizard surfaces should deep-link back to the lifecycle step they belong to

## 5. Data boundaries and persistence rules
- UI edits documented lifecycle structures only
- Respect PWA/node boundary: lifecycle truth remains in core tables, not browser cache
- Keep transitions traceable to lifecycle schema docs

## 6. Docs to read first
- Nexus bundle: `DOC22_Lifecycle_State_Machine.md`
- Nexus bundle: `DOC23_Mode_Lifecycle_Overlays.md`
- `05_DATA_SCHEMA_AND_TABLES/48_LIFECYCLE_STATE_TABLE_SCHEMA.md`
- `05_DATA_SCHEMA_AND_TABLES/DOC121_Node_vs_PWA_Data_Boundary.md`
- `05_DATA_SCHEMA_AND_TABLES/DOC122_Mode_Ownership_Table_Map.md`

## 7. Drift reconciliation note
Older docs may treat Comms as a mode transition inside Nexus. Re-route those transitions toward Omni handoff points where applicable. Map Social/Growth transitions into Grow Hub. Keep Work, Money, Office, and Grow as the four operational hub anchors.

## 8. Scope included
- Lifecycle step list
- Transition editor
- State metadata editor
- Validation rules UI
- Preview flow summary

## 9. Scope excluded
- Full workflow execution engine
- Omni channel-specific flows
- Dispatch screens

## 10. Acceptance criteria
- [ ] Can view and edit ordered lifecycle states
- [ ] Can attach transition metadata to states
- [ ] Reflects current hub model and Omni handoffs
- [ ] Mobile-first interaction works without desktop-only gestures

## 11. Suggested component breakdown
- `LifecycleStepList`
- `StepCard`
- `TransitionEditor`
- `StateMetaPanel`
- `ValidationSummary`

## 12. Suggested API/data dependencies
- lifecycle states endpoint
- transition rules endpoint
- ownership/hub mapping data

## 13. Output expectation for Spark
Produce a standalone React mini-app for lifecycle editing with mobile-first layout and contract-based persistence assumptions.

---
**Labels:** `spark`, `nexus`, `lifecycle`, `scout`, `mobile`
