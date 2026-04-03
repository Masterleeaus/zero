# TITAN SIGNAL ENVELOPE — FINAL SPECIFICATION

**Version:** Prompt 6  
**Status:** Phase 6.3 Compliant

---

## Required Fields (Phase 6.3)

Every signal envelope produced by `EnvelopeBuilder::build()` MUST contain:

| Field | Type | Description |
|-------|------|-------------|
| `signal_uuid` | string | Globally unique envelope identifier (`suuid-*`) |
| `company_id` | int\|null | Tenant boundary — never null in production |
| `origin` | string | Source of the signal (`server`, `mcp`, `omni`, `api`) |
| `intent` | string | Business intent key (e.g., `signal.envelope`, `work.job.created`) |
| `state` | string | Current lifecycle state (`new`, `processing`, `approved`, `rejected`, `rewound`) |
| `approval_required` | bool | True if any signal in the envelope requires approval |
| `rewind_eligible` | bool | True if this envelope can be rewound |
| `timestamp` | ISO 8601 | When the envelope was built |

---

## Full Envelope Structure

```json
{
  "signal_uuid": "suuid-66f3a1b2-4d9e-11ef-b123",
  "company_id": 42,
  "origin": "server",
  "intent": "work.job.created",
  "state": "new",
  "approval_required": false,
  "rewind_eligible": true,
  "timestamp": "2026-04-03T05:28:32Z",

  "id": "env-66f3a1b2-4d9e-11ef-abcd",
  "team_id": null,
  "actor_id": 7,
  "summary": "Signal envelope with 2 signals",
  "headline": { "signal_id": "sig-001", "type": "work.job.created", ... },
  "signals": [ ... ],
  "top_signals": [ ... ],
  "meta": {
    "signal_count": 2,
    "severity_counts": { "GREEN": 2, "AMBER": 0, "RED": 0 },
    "requires_approval_count": 0,
    "approval_queue": []
  },
  "risk": {
    "priority": "low",
    "approval_pressure": "clear",
    "top_priority_band": "low"
  },
  "timeline_hint": {
    "latest_signal_at": "2026-04-03T05:28:00Z",
    "oldest_signal_at": "2026-04-03T05:27:00Z"
  }
}
```

---

## Subscriber Compatibility

The envelope is consumed by:

| Subscriber | Purpose |
|-----------|---------|
| `ZeroSubscriber` | Core state tracking and telemetry |
| `PulseSubscriber` | Automation trigger evaluation |
| `RewindSubscriber` | Rewind snapshot creation |

---

## ApprovalChain Integration

When `approval_required: true`:
1. `ApprovalChain::determine()` resolves approver roles from signal severity + rules.
2. Approval state is recorded in `tz_signals.status = 'awaiting-approval'`.
3. On rejection: `state` transitions to `rejected`; `rewind_eligible: true` activates Rewind path.
4. On approval correction: `state` transitions to `corrected`; linked to original via `rewind_from`.

---

## Validation

`SignalValidator::validate()` enforces:
- Required fields: `company_id`, `type`, `kind`, `payload`
- Signal type must be registered in `SignalRegistry`
- Severity must match allowed values for type
- Payload required fields per type definition
- No duplicate signals for same `process_id` + `type`

Envelopes with missing required Phase 6.3 fields are considered non-compliant and will be rejected by the MCP layer.

---

## ProcessRecorder + AuditTrail Integration

After dispatch:
- `ProcessRecorder` records the state transition in `tz_process_states`
- `AuditTrail::recordEntry()` logs `signal.dispatched` or `signal.dispatch_failed` in `tz_audit_log`

---

## Rewind + RewindSubscriber

`RewindSubscriber::handle()` evaluates every envelope and:
1. Creates a `RewindSnapshot` for `rewind_eligible: true` envelopes.
2. Links snapshot to the parent `RewindCase` if one exists.
3. On approval rejection: triggers `RewindEngine::begin()` to prepare correction path.
