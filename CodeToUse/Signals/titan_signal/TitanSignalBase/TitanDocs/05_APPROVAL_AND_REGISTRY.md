# Approval Queue and Signal Registry

This pass adds two core upgrade pieces:

- `tz_approval_queue` for persistent approval work
- `SignalRegistry` for canonical signal definitions

## Signal Registry

Registry now lives in `config/titan_signal.php` and defines:

- domain
- kind
- required payload fields
- allowed process states
- approval rules

## Approval Queue

Signals that require human review now persist to `tz_approval_queue`.

Fields include:

- `process_id`
- `signal_id`
- `company_id`
- `approval_chain`
- `current_approver`
- `status`
- `decision_meta`

## New API Endpoints

- `GET /api/signals/approvals`
- `POST /api/signals/approvals/{processId}`

## State Impact

When approval is required:

`awaiting-processing -> awaiting-approval`

When approved:

`awaiting-approval -> processing`

When rejected:

`awaiting-approval -> approval-rejected`
