# [spark] Scout Mode Configurator

## Docs to read first
- Nexus bundle: DOC12_Scout_Layer.md
- Nexus bundle: DOC83_Admin_Mode_Blueprint.md
- Nexus bundle: DOC18_View_Surface_Map.md
- Nexus bundle: DOC38_Master_Development_Roadmap.md
- Nexus bundle: DOC145_Final_Prebuild_Checklist.md

Purpose
Build the configuration UI for Scout rules that shape hub behavior across Work, Money, Office, and Grow.

Why this app is priority
Nexus cannot behave consistently until operating policies are editable in one clear place.

Mobile-first UX requirements
- Sectioned cards by hub
- Toggle / select / threshold controls sized for touch
- Save and preview actions visible without desktop-only patterns
- Inline policy descriptions and warnings

Chat-launch / wizard-launch behavior
- Launchable from Nexus Home chat via intents like “change invoice automation rule” or “require photos for completions”
- Quick links from each hub should open directly to that hub’s policy section

Data boundaries and persistence rules
- Config UI edits policy records only
- No local runtime state as source of truth
- Persist via core scout/rule tables or documented policy models only

Docs to read first
- DOC12_Scout_Layer.md
- DOC83_Admin_Mode_Blueprint.md
- DOC18_View_Surface_Map.md
- DOC38_Master_Development_Roadmap.md
- DOC145_Final_Prebuild_Checklist.md

Drift reconciliation note
Where docs still describe old mode names, re-map to current hub names. Comms rules belong to Omni, not this app.

Scope included
- Per-hub policy sections
- Toggle controls
- threshold controls
- summaries / preview text
- validation and confirmation states

Scope excluded
- Full automation execution engine
- Omni channel routing rules
- Deep analytics dashboards

Acceptance criteria
- Mobile-first editor grouped by Work, Money, Office, Grow
- Editable rule cards with save/cancel/validation states
- Can be opened to a specific hub section directly
- Uses contract-friendly persistence boundaries

Suggested component breakdown
- HubPolicyTabs or accordion
- RuleCard
- ThresholdField
- ToggleField
- SaveBar
- PolicySummaryPanel

Suggested API/data dependencies
- scout rules list endpoint
- scout rules update endpoint
- hub metadata map

Output expectation for Spark
Produce a standalone React mini-app that edits Scout configuration cleanly and can later be embedded inside Office Hub.
