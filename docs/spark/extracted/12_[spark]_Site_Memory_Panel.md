# [spark] Site Memory Panel

## Docs to read first
- Nexus bundle: DOC80_Jobs_Mode_Blueprint.md
- 05_DATA_SCHEMA_AND_TABLES/DOC122_Mode_Ownership_Table_Map.md
- 05_DATA_SCHEMA_AND_TABLES/DOC121_Node_vs_PWA_Data_Boundary.md
- Nexus bundle: DOC83_Admin_Mode_Blueprint.md
- 09_MOBILE_APPS_AND_CLIENT_SURFACES/25_MOBILE_SURFACE_CAPABILITY_MATRIX.md

Purpose
Build the panel for persistent site memory: access instructions, hazards, preferences, restrictions, and recurring context.

Why this app is priority
Site memory is a strong product differentiator and improves repeat-service quality immediately.

Mobile-first UX requirements
- Read-first layout for in-field use
- Edit controls available but secondary
- Sections for access, hazards, preferences, restrictions, recurring notes
- Fast scan on small screens

Chat-launch / wizard-launch behavior
- Launch from Work Hub, Today Jobs Panel, Job Setup, or chat intents like “show site notes”
- Can be opened in read mode or edit mode depending context

Data boundaries and persistence rules
- Persist site memory to scoped operational memory structures linked to tenant/site/job context
- Keep local cache secondary to core truth

Docs to read first
- DOC80_Jobs_Mode_Blueprint.md
- DOC122_Mode_Ownership_Table_Map.md
- DOC121_Node_vs_PWA_Data_Boundary.md
- DOC83_Admin_Mode_Blueprint.md
- 25_MOBILE_SURFACE_CAPABILITY_MATRIX.md

Drift reconciliation note
Treat site memory as Work/Office shared operational memory, not as a generic notes widget or old-mode artifact.

Scope included
- access instructions
- hazards / restrictions
- customer preferences
- recurring service notes
- edit/read modes

Scope excluded
- full CRM contact history
- dispatch board
- invoice/payment logic

Acceptance criteria
- Site memory renders clearly on mobile
- Supports read and edit modes
- Can be launched from Work flows and chat
- Data model assumptions respect tenancy and operational memory boundaries

Suggested component breakdown
- SiteMemoryHeader
- AccessSection
- HazardSection
- PreferencesSection
- RecurringNotesSection
- EditToggleBar

Suggested API/data dependencies
- site memory read endpoint
- site memory update endpoint
- site metadata summary endpoint

Output expectation for Spark
Produce a standalone React mini-app for site memory that is mobile-first, field-friendly, and composable inside Work and Office contexts.
