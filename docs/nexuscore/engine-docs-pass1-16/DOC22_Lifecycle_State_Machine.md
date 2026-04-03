# DOC 22 — Lifecycle State Machine

Canonical state sequence:

initiated
→ signal_queued
→ awaiting_validation
→ validation_approved
→ awaiting_processing
→ awaiting_approval
→ processing
→ processed

Recovery / exception states:
- validation_rejected
- approval_rejected
- processing_error
- processing_hold
- conflict_hold
- rewinding
- awaiting_correction
- rolled_back
- archived

Rules:
- invalid transitions are forbidden
- every transition is logged
- every transition emits a state-change signal
