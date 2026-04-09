# [spark] Scout Signal Rules Engine

## 1. Purpose
Build the signal-rules configuration UI that defines what conditions create alerts, suggestions, or approvals. Signal rules fire when lifecycle or operational thresholds are crossed.

## 2. Why this app is priority
Signals turn passive data into actionable system behavior. Without configurable signal rules, the system cannot surface the right alerts at the right time.

## 3. Mobile-first UX requirements
- Rules as stacked cards
- Condition builders simplified for touch
- Trigger severity visually clear
- Preview examples inline

## 4. Chat-launch / wizard-launch behavior
- Launch from intents like "alert me when jobs are late" or "flag overdue invoices after 5 days"
- Link from signal strip items back into the rule that generated them

## 5. Data boundaries and persistence rules
- Edit signal rules only
- Persist through documented policy/rule models
- No operational truth in local UI state

## 6. Docs to read first
- Nexus bundle: `DOC12_Scout_Layer.md`
- Nexus bundle: `DOC22_Lifecycle_State_Machine.md`
- Nexus bundle: `DOC23_Mode_Lifecycle_Overlays.md`
- `04_FINANCE_AND_MONEY/DOC82_Finance_Mode_Blueprint.md`
- Nexus bundle: `DOC83_Admin_Mode_Blueprint.md`

## 7. Drift reconciliation note
Any older Comms-trigger content should be split: operational signals (job late, invoice overdue, checklist incomplete) stay in this app. Conversation/channel signals belong to Omni. Social/growth signals belong to Grow Hub.

## 8. Scope included
- Rule list
- Create/edit signal rule
- Severity and threshold controls
- Preview examples
- Enable/disable state

## 9. Scope excluded
- Actual notification delivery systems
- Omni inbox alert surfaces
- Analytics warehouse reporting

## 10. Acceptance criteria
- [ ] User can create and edit at least basic threshold rules
- [ ] Rules can be associated to Work, Money, Office, or Grow
- [ ] UI shows severity and summary clearly on mobile
- [ ] Saved rules are represented via contracts, not local-only state

## 11. Suggested component breakdown
- `SignalRuleList`
- `SignalRuleCard`
- `ConditionBuilderLite`
- `SeveritySelector`
- `RulePreviewPanel`

## 12. Suggested API/data dependencies
- signal rules endpoint
- signal categories list
- severity/options metadata

## 13. Output expectation for Spark
Produce a standalone React mini-app for configuring Nexus operational signals, mobile-first and ready to embed into Office Hub.

---
**Labels:** `spark`, `nexus`, `scout`, `signals`, `mobile`, `office-hub`
