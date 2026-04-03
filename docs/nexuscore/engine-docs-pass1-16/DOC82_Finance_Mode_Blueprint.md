# Finance Mode Blueprint

## Scope
Revenue visibility and payment lifecycle.

## Entities
Invoice → Payment → Adjustment → Reminder → Settlement → LedgerEvent

## Responsibilities
- Invoice lifecycle
- Payment capture tracking
- Overdue workflows
- Revenue summaries
- Approval gates

## Signals
invoice.created
invoice.sent
invoice.overdue
payment.received
payment.failed
