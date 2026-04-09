# [spark] Field Checklist Runner Panel

## 1. Purpose
Build the mobile-first checklist execution panel for field workers, including completion, notes, issue flags, and proof capture hooks. This is the in-field execution interface used during active job delivery.

## 2. Why this app is priority
It is one of the highest-value operational panels in the system and directly impacts service quality. Field workers need a fast, offline-capable checklist runner designed specifically for phone use.

## 3. Mobile-first UX requirements
- Designed primarily for phone use in the field
- One task per row/card with obvious completion state
- Fast note and issue capture
- Camera/proof hooks visible and easy to use

## 4. Chat-launch / wizard-launch behavior
- Launch from Today Jobs Panel, Job Setup flow, or chat intents like "open today's checklist"
- Can return control to Completion-to-Invoice wizard when finished

## 5. Data boundaries and persistence rules
- Checklist item truth must persist to core structures
- Temporary offline queue/cache is allowed but must sync back to the source of truth
- Follow `DOC121_Node_vs_PWA_Data_Boundary.md` boundaries strictly

## 6. Docs to read first
- Nexus bundle: `DOC80_Jobs_Mode_Blueprint.md`
- `09_MOBILE_APPS_AND_CLIENT_SURFACES/25_MOBILE_SURFACE_CAPABILITY_MATRIX.md`
- `09_MOBILE_APPS_AND_CLIENT_SURFACES/MOBILE_STACK_ALIGNMENT.md`
- `05_DATA_SCHEMA_AND_TABLES/DOC121_Node_vs_PWA_Data_Boundary.md`
- `09_MOBILE_APPS_AND_CLIENT_SURFACES/26_CHATBOT_BUILDER_TO_PWA_BRIDGE.md`

## 7. Drift reconciliation note
Keep the app in Work Hub / field-worker language; do not drift back into generic task terminology if job/checklist concepts are already defined in the docs bundle. Offline sync is a PWA concern; it must not store operational truth as its authoritative source.

## 8. Scope included
- Checklist items
- Completion toggles
- Notes per item
- Issue flagging
- Proof/photo action hooks
- Progress summary

## 9. Scope excluded
- Full route management
- Billing logic
- CRM editing

## 10. Acceptance criteria
- [ ] Mobile-first field panel works well on phones
- [ ] Can complete items, add notes, and flag issues
- [ ] Supports proof capture hooks and completion summaries
- [ ] Can hand off to next lifecycle step (Completion-to-Invoice wizard)

## 11. Suggested component breakdown
- `ChecklistHeader`
- `ChecklistItemCard`
- `NotesDrawer`
- `IssueFlagModal`
- `ProofActionBar`
- `ProgressFooter`

## 12. Suggested API/data dependencies
- checklist items endpoint
- checklist update endpoint
- proof attachment endpoint or stub
- issue creation endpoint or stub

## 13. Output expectation for Spark
Produce a standalone React mini-app optimized for field execution with PWA-friendly assumptions and composable lifecycle handoffs.

---
**Labels:** `spark`, `nexus`, `work-hub`, `mobile`, `field-worker`, `PWA`
