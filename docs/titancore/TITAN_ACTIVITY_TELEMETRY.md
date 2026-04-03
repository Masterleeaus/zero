# TITAN ACTIVITY TELEMETRY

## Overview

Every AI action in TitanCore is audited. No silent execution is permitted (Phase 5.10).

## Audit Trail

`AuditTrail` (`app/Titan/Signals/AuditTrail.php`) provides two recording methods:

### `recordEntry()` – Legacy / Signals
Used by `SignalDispatcher` for signal lifecycle events. Writes to `tz_audit_log`.

### `recordActivity()` – AI Actions (Phase 5.10)
Enriched method for AI-specific events. Requires:
- `company_id`
- `user_id`
- `intent`
- `provider`
- Optionally: `tokens`, `duration`, `status`, `signal_uuid`

Also dispatches `TitanActivityEvent` to broadcast the activity to the real-time feed.

### Actions Audited

| Action | Trigger |
|--------|---------|
| `ai.completion` | AI text/image/voice completion |
| `memory.write` | Memory entry persisted |
| `skill.execution` | Zylos skill executed |
| `signal.dispatched` | Signal dispatched to subscribers |
| `approval.triggered` | AI approval gate activated |
| `rewind.correction` | Rewind engine applied correction |
| `signal.dispatch_failed` | Signal dispatch failed |

## Real-Time Activity Feed

Channel: `titan.core.activity` (broadcast)

Event: `.titan.activity`

Payload:
```json
{
  "intent": "text.complete",
  "provider": "openai",
  "duration": 420,
  "tokens": 1200,
  "company_id": 5,
  "user_id": 42,
  "status": "ok",
  "event_type": "ai.completion",
  "timestamp": "2026-04-03T05:28:24Z"
}
```

Channel authorization: admin users only (`user->is_admin`).

## Database Table

`tz_audit_log` columns:
- `process_id` – TitanCore process UUID
- `signal_id` – Associated signal UUID
- `action` – Event type string
- `performed_by` – User ID
- `details` – JSON blob with full context
- `created_at` – Timestamp

## Admin Panel

View at: `/dashboard/admin/titan/core/activity`

Shows:
- Live WebSocket feed (requires broadcasting configured)
- Server-side table of last 100 audit entries
