# Titan Signal Base

Titan Signal is the process-first signal ingestion layer for Titan Zero, Pulse, and Rewind.

## Current Scope

- process recording to `tz_processes`
- persisted state transitions in `tz_process_states`
- canonical signal storage in `tz_signals`
- pending/failed queue in `tz_signal_queue`
- audit ledger in `tz_audit_log`
- validation and approval hints
- dispatcher with Zero, Pulse, and Rewind subscribers
- priority scoring and ranked envelopes
- persistent multi-step approval queue
- company-scope guarded API reads/writes

## Primary Flow

`process -> signal -> validation -> approval hint -> queue -> dispatch -> envelope/timeline`

## New Endpoints in this pass

- `POST /api/signals/publish`
- `GET /api/signals/registry`
- `GET /api/signals/processes/{processId}`
- ranked `GET /api/signals/feed?ranked=1`

## Notes

- queue dispatch now retries failed rows and stores subscriber errors
- approval decisions support `approved`, `rejected`, and `hold`
- final approval can advance across multiple approvers before processing
