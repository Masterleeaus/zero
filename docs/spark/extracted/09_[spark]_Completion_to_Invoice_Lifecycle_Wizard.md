# [spark] Completion-to-Invoice Lifecycle Wizard

## Docs to read first
- Nexus bundle: DOC80_Jobs_Mode_Blueprint.md
- 04_FINANCE_AND_MONEY/DOC82_Finance_Mode_Blueprint.md
- Nexus bundle: DOC22_Lifecycle_State_Machine.md
- 05_DATA_SCHEMA_AND_TABLES/DOC122_Mode_Ownership_Table_Map.md
- 05_DATA_SCHEMA_AND_TABLES/48_LIFECYCLE_STATE_TABLE_SCHEMA.md

Purpose
Build the wizard that validates completion, checks proof, and launches invoice creation / payment request actions.

Why this app is priority
It closes the service-to-revenue loop.

Mobile-first UX requirements
- Minimal steps with strong confirmation patterns
- Completion proof summary is easy to scan on phone
- Invoice summary fits single-column layout

Chat-launch / wizard-launch behavior
- Launch from checklist completion, Work Hub suggestions, or chat intents like “invoice completed job”
- Surface next actions such as send payment request or request review

Data boundaries and persistence rules
- Completion truth, invoice truth, and lifecycle updates must land in documented core structures
- No local-only completion state as authoritative source

Docs to read first
- DOC80_Jobs_Mode_Blueprint.md
- DOC82_Finance_Mode_Blueprint.md
- DOC22_Lifecycle_State_Machine.md
- DOC122_Mode_Ownership_Table_Map.md
- 48_LIFECYCLE_STATE_TABLE_SCHEMA.md

Drift reconciliation note
Treat finance outputs as Money Hub outcomes linked from Work, not a combined legacy mode flow.

Scope included
- completion validation
- proof summary
- invoice draft preview
- payment request option
- final lifecycle transition

Scope excluded
- full accounts receivable dashboard
- payroll and margin analytics
- review campaign engine

Acceptance criteria
- Wizard can confirm completion and create/send invoice handoff
- Shows proof summary and billing summary on mobile
- Updates lifecycle state via defined contracts
- Exposes next action links to Money Hub

Suggested component breakdown
- CompletionCheckStep
- ProofSummaryCard
- BillingSummaryStep
- PaymentRequestOptions
- FinalizeTransitionStep

Suggested API/data dependencies
- job completion endpoint
- proof/photos summary endpoint
- invoice draft endpoint
- payment request endpoint

Output expectation for Spark
Produce a standalone React wizard that is mobile-first, lifecycle-aware, and composable into Work and Money flows.
