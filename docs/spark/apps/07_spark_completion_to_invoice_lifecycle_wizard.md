# [spark] Completion-to-Invoice Lifecycle Wizard

## 1. Purpose
Build the wizard that validates completion, checks proof, and launches invoice creation / payment request actions. It closes the service-to-revenue loop between Work Hub and Money Hub.

## 2. Why this app is priority
It closes the service-to-revenue loop. Without this wizard, a completed job cannot trigger billing in a structured, lifecycle-aware way.

## 3. Mobile-first UX requirements
- Minimal steps with strong confirmation patterns
- Completion proof summary is easy to scan on phone
- Invoice summary fits single-column layout

## 4. Chat-launch / wizard-launch behavior
- Launch from checklist completion, Work Hub suggestions, or chat intents like "invoice completed job"
- Surface next actions such as send payment request or request review

## 5. Data boundaries and persistence rules
- Completion truth, invoice truth, and lifecycle updates must land in documented core structures
- No local-only completion state as authoritative source

## 6. Docs to read first
- Nexus bundle: `DOC80_Jobs_Mode_Blueprint.md`
- `04_FINANCE_AND_MONEY/DOC82_Finance_Mode_Blueprint.md`
- Nexus bundle: `DOC22_Lifecycle_State_Machine.md`
- `05_DATA_SCHEMA_AND_TABLES/DOC122_Mode_Ownership_Table_Map.md`
- `05_DATA_SCHEMA_AND_TABLES/48_LIFECYCLE_STATE_TABLE_SCHEMA.md`

## 7. Drift reconciliation note
Treat finance outputs as Money Hub outcomes linked from Work Hub, not a combined legacy mode flow. The old Finance Mode is now Money Hub. Work Hub owns completion; Money Hub owns billing. This wizard is the handoff point between the two.

## 8. Scope included
- Completion validation step
- Proof summary card
- Invoice draft preview
- Payment request option
- Final lifecycle transition step

## 9. Scope excluded
- Full accounts receivable dashboard
- Payroll and margin analytics
- Review campaign engine

## 10. Acceptance criteria
- [ ] Wizard can confirm completion and create/send invoice handoff
- [ ] Shows proof summary and billing summary on mobile
- [ ] Updates lifecycle state via defined contracts
- [ ] Exposes next action links to Money Hub

## 11. Suggested component breakdown
- `CompletionCheckStep`
- `ProofSummaryCard`
- `BillingSummaryStep`
- `PaymentRequestOptions`
- `FinalizeTransitionStep`

## 12. Suggested API/data dependencies
- job completion endpoint
- proof/photos summary endpoint
- invoice draft endpoint
- payment request endpoint

## 13. Output expectation for Spark
Produce a standalone React wizard that is mobile-first, lifecycle-aware, and composable into Work and Money flows.

---
**Labels:** `spark`, `nexus`, `lifecycle`, `wizard`, `work-hub`, `money-hub`, `mobile`
