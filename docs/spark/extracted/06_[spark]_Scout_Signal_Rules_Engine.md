# [spark] Scout Signal Rules Engine

## Docs to read first
- Nexus bundle: DOC12_Scout_Layer.md
- Nexus bundle: DOC22_Lifecycle_State_Machine.md
- Nexus bundle: DOC23_Mode_Lifecycle_Overlays.md
- 04_FINANCE_AND_MONEY/DOC82_Finance_Mode_Blueprint.md
- Nexus bundle: DOC83_Admin_Mode_Blueprint.md

Purpose
Build the signal-rules configuration UI that defines what conditions create alerts, suggestions, or approvals.

Why this app is priority
Signals turn passive data into actionable system behavior.

Mobile-first UX requirements
- Rules as stacked cards
- Condition builders simplified for touch
- Trigger severity visually clear
- Preview examples inline

Chat-launch / wizard-launch behavior
- Launch from intents like “alert me when jobs are late” or “flag overdue invoices after 5 days”
- Link from signal strip items into the rule that generated them

Data boundaries and persistence rules
- Edit signal rules only
- Persist through documented policy/rule models
- No operational truth in local UI state

Docs to read first
- DOC12_Scout_Layer.md
- DOC22_Lifecycle_State_Machine.md
- DOC23_Mode_Lifecycle_Overlays.md
- DOC82_Finance_Mode_Blueprint.md
- DOC83_Admin_Mode_Blueprint.md

Drift reconciliation note
Any older Comms-trigger content should be split: operational signals stay here, conversation/channel signals belong to Omni.

Scope included
- rule list
- create/edit signal rule
- severity and threshold controls
- preview examples
- enable/disable state

Scope excluded
- actual notification delivery systems
- Omni inbox alert surfaces
- analytics warehouse reporting

Acceptance criteria
- User can create and edit at least basic threshold rules
- Rules can be associated to Work, Money, Office, or Grow
- UI shows severity and summary clearly on mobile
- Saved rules are represented via contracts, not local-only state

Suggested component breakdown
- SignalRuleList
- SignalRuleCard
- ConditionBuilderLite
- SeveritySelector
- RulePreviewPanel

Suggested API/data dependencies
- signal rules endpoint
- signal categories list
- severity/options metadata

Output expectation for Spark
Produce a standalone React mini-app for configuring Nexus operational signals, mobile-first and ready to embed into Office Hub.
