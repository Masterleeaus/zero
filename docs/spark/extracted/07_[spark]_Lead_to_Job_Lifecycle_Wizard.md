# [spark] Lead-to-Job Lifecycle Wizard

## Docs to read first
- Nexus bundle: DOC22_Lifecycle_State_Machine.md
- Nexus bundle: DOC23_Mode_Lifecycle_Overlays.md
- Nexus bundle: DOC80_Jobs_Mode_Blueprint.md
- 05_DATA_SCHEMA_AND_TABLES/DOC122_Mode_Ownership_Table_Map.md
- 09_MOBILE_APPS_AND_CLIENT_SURFACES/26_CHATBOT_BUILDER_TO_PWA_BRIDGE.md

Purpose
Build a guided wizard that converts a lead or enquiry into a runnable job path.

Why this app is priority
It creates the commercial-to-operational bridge that the system needs immediately.

Mobile-first UX requirements
- Stepper that works well on phones
- Few fields per step
- Resume state clearly shown
- Clear CTA on each step

Chat-launch / wizard-launch behavior
- Launch from Nexus Home, Grow Hub, or Omni handoff intents such as “book this lead” or “turn enquiry into a job”
- Chat should be able to prefill step data

Data boundaries and persistence rules
- Persist contact/site/job-related truth into core lifecycle structures and mapped ownership tables
- Browser state is temporary only

Docs to read first
- DOC22_Lifecycle_State_Machine.md
- DOC23_Mode_Lifecycle_Overlays.md
- DOC80_Jobs_Mode_Blueprint.md
- DOC122_Mode_Ownership_Table_Map.md
- 26_CHATBOT_BUILDER_TO_PWA_BRIDGE.md

Drift reconciliation note
If docs mention Social/Comms initiation inside Nexus, reinterpret those as Grow inputs or Omni handoffs into this wizard.

Scope included
- lead details
- site details
- service details
- schedule preference
- checklist template selection or placeholder
- final confirmation

Scope excluded
- full dispatch board
- full invoice generation
- Omni thread UI

Acceptance criteria
- Wizard can create a valid lead-to-job draft flow
- Mobile-first step layout works on small screens
- Supports prefill from chat/handoff context
- Final step exposes next actions toward job setup

Suggested component breakdown
- WizardStepper
- LeadInfoStep
- SiteInfoStep
- ServiceConfigStep
- ScheduleStep
- ConfirmationStep

Suggested API/data dependencies
- contact create/update endpoint
- site create/update endpoint
- service type metadata
- lifecycle create endpoint

Output expectation for Spark
Produce a standalone React wizard mini-app that is mobile-first, composable, and launchable from chat or hub actions.
