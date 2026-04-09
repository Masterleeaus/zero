# [spark] Scout Mode Configurator

## 1. Purpose
Build the configuration UI for Scout rules that shape hub behavior across Work, Money, Office, and Grow. Scout policies define automation rules, thresholds, and approval requirements for each hub.

## 2. Why this app is priority
Nexus cannot behave consistently until operating policies are editable in one clear place. This app provides that control surface.

## 3. Mobile-first UX requirements
- Sectioned cards by hub (Work, Money, Office, Grow)
- Toggle / select / threshold controls sized for touch
- Save and preview actions visible without desktop-only patterns
- Inline policy descriptions and warnings

## 4. Chat-launch / wizard-launch behavior
- Launchable from Nexus Home chat via intents like "change invoice automation rule" or "require photos for completions"
- Quick links from each hub should open directly to that hub's policy section

## 5. Data boundaries and persistence rules
- Config UI edits policy records only
- No local runtime state as source of truth
- Persist via core scout/rule tables or documented policy models only

## 6. Docs to read first
- Nexus bundle: `DOC12_Scout_Layer.md`
- Nexus bundle: `DOC83_Admin_Mode_Blueprint.md`
- Nexus bundle: `DOC18_View_Surface_Map.md`
- Nexus bundle: `DOC38_Master_Development_Roadmap.md`
- Nexus bundle: `DOC145_Final_Prebuild_Checklist.md`

## 7. Drift reconciliation note
Where docs still describe old mode names, re-map to current hub names: Jobs Mode → Work Hub, Finance Mode → Money Hub, Admin Mode → Office Hub. Comms rules belong to Omni, not this app. Do not carry forward any comms or social media configuration into Scout.

## 8. Scope included
- Per-hub policy sections
- Toggle controls
- Threshold controls
- Summaries / preview text
- Validation and confirmation states

## 9. Scope excluded
- Full automation execution engine
- Omni channel routing rules
- Deep analytics dashboards

## 10. Acceptance criteria
- [ ] Mobile-first editor grouped by Work, Money, Office, Grow
- [ ] Editable rule cards with save/cancel/validation states
- [ ] Can be opened to a specific hub section directly
- [ ] Uses contract-friendly persistence boundaries

## 11. Suggested component breakdown
- `HubPolicyTabs` (or accordion)
- `RuleCard`
- `ThresholdField`
- `ToggleField`
- `SaveBar`
- `PolicySummaryPanel`

## 12. Suggested API/data dependencies
- scout rules list endpoint
- scout rules update endpoint
- hub metadata map

## 13. Output expectation for Spark
Produce a standalone React mini-app that edits Scout configuration cleanly and can later be embedded inside Office Hub.

---
**Labels:** `spark`, `nexus`, `scout`, `mobile`, `office-hub`
