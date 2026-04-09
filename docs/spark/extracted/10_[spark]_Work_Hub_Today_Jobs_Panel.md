# [spark] Work Hub Today Jobs Panel

## Docs to read first
- Nexus bundle: DOC80_Jobs_Mode_Blueprint.md
- Nexus bundle: DOC18_View_Surface_Map.md
- 09_MOBILE_APPS_AND_CLIENT_SURFACES/25_MOBILE_SURFACE_CAPABILITY_MATRIX.md
- 09_MOBILE_APPS_AND_CLIENT_SURFACES/MOBILE_STACK_ALIGNMENT.md
- 05_DATA_SCHEMA_AND_TABLES/DOC121_Node_vs_PWA_Data_Boundary.md

Purpose
Build the mobile-first Today panel for Work Hub showing today’s jobs, alerts, and next actions.

Why this app is priority
It is the day-to-day operational heartbeat for staff and dispatchers.

Mobile-first UX requirements
- Single-column job cards
- Sticky today summary
- Large action buttons
- Fast loading and clear offline-aware states

Chat-launch / wizard-launch behavior
- Launch from Nexus Home or direct hub zoom
- Chat should be able to open filtered views such as “show late jobs” or “show unassigned work”

Data boundaries and persistence rules
- List and filter jobs via core endpoints
- UI may cache for responsiveness, but truth remains in core job and lifecycle structures

Docs to read first
- DOC80_Jobs_Mode_Blueprint.md
- DOC18_View_Surface_Map.md
- 25_MOBILE_SURFACE_CAPABILITY_MATRIX.md
- MOBILE_STACK_ALIGNMENT.md
- DOC121_Node_vs_PWA_Data_Boundary.md

Drift reconciliation note
Translate older Jobs Mode references into Work Hub terminology.

Scope included
- today summary strip
- job list cards
- status chips
- quick actions to open setup/checklist/completion flows
- empty/loading/error states

Scope excluded
- full drag-and-drop dispatch board
- advanced route optimization
- payroll or invoice analytics

Acceptance criteria
- Mobile-first job list is usable as standalone panel
- Supports filtered states such as late / unassigned / at risk
- Quick actions deep-link into related wizards
- Ready to embed inside future Work Hub shell

Suggested component breakdown
- TodaySummaryStrip
- JobCardList
- JobCard
- FilterChips
- QuickActionBar

Suggested API/data dependencies
- today jobs endpoint
- job status summary endpoint
- action launch map

Output expectation for Spark
Produce a standalone React mini-app that can serve as the Work Hub Today surface on phone-sized screens.
