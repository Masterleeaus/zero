# [spark] Job Setup Lifecycle Wizard

## Docs to read first
- Nexus bundle: DOC80_Jobs_Mode_Blueprint.md
- Nexus bundle: DOC18_View_Surface_Map.md
- Nexus bundle: DOC22_Lifecycle_State_Machine.md
- 09_MOBILE_APPS_AND_CLIENT_SURFACES/25_MOBILE_SURFACE_CAPABILITY_MATRIX.md
- 05_DATA_SCHEMA_AND_TABLES/DOC121_Node_vs_PWA_Data_Boundary.md

Purpose
Build the wizard that turns a created job into a properly prepared operational record with checklist, crew, notes, recurrence, and execution details.

Why this app is priority
A job that is not properly prepared cannot be executed well in the field.

Mobile-first UX requirements
- Multi-step but fast
- Inline help for field-specific concepts
- Large controls for crew assignment and checklist selections
- Resume and save-progress states

Chat-launch / wizard-launch behavior
- Launch from chat intents like “set up tomorrow’s office clean” or from the end of Lead-to-Job wizard
- Deep-link from Work Hub cards such as unprepared job alerts

Data boundaries and persistence rules
- Persist preparation truth to core job/checklist/site structures only
- Temporary wizard state can exist locally, but not as source of truth

Docs to read first
- DOC80_Jobs_Mode_Blueprint.md
- DOC18_View_Surface_Map.md
- DOC22_Lifecycle_State_Machine.md
- 25_MOBILE_SURFACE_CAPABILITY_MATRIX.md
- DOC121_Node_vs_PWA_Data_Boundary.md

Drift reconciliation note
Use current Work Hub language instead of older Jobs Mode UI language when writing acceptance criteria and component names.

Scope included
- crew assignment
- checklist selection or creation handoff
- access notes
- recurrence settings
- operational readiness summary

Scope excluded
- live route optimization
- full dispatch board
- payroll / invoicing

Acceptance criteria
- Wizard completes a practical job setup flow
- Mobile-first and field-ready
- Can launch from chat or previous wizard
- Produces a readiness summary with next step links

Suggested component breakdown
- JobMetaStep
- CrewAssignmentStep
- ChecklistConfigStep
- AccessNotesStep
- RecurrenceStep
- ReadinessSummaryStep

Suggested API/data dependencies
- job detail endpoint
- crew/team availability endpoint
- checklist templates endpoint
- recurrence options metadata

Output expectation for Spark
Produce a standalone mobile-first React wizard that prepares jobs for execution and later nests cleanly inside Work Hub.
