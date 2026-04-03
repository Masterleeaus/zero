# Priority, Process Views, and Guard Rails

This pass upgrades Titan Signal in four practical ways.

## 1. Priority Engine

`SignalPriorityEngine` assigns:

- numeric score
- priority band
- machine-readable reasons

This score now feeds ranked feeds and envelope headlines.

## 2. Process Detail API

`GET /api/signals/processes/{processId}` returns:

- process core row
- state history
- linked signals
- audit timeline
- approval queue rows

This gives Zero, Pulse, and developers one canonical inspection endpoint.

## 3. Approval Progression

Approval queue now tracks:

- `approval_chain`
- `approved_by`
- `current_approver`
- `status`

Approvals can progress through multiple approvers before the process enters `processing`.

## 4. Queue Failure Visibility

`tz_signal_queue` now stores `last_error` and failed rows are retried by `dispatchPending()`.

## Scope Guard

API writes and reads that accept `company_id` now reject cross-company access when an authenticated user belongs to a different tenant.
