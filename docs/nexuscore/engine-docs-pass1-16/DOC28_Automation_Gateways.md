# DOC 28 — Automation Gateways

Automation does not bypass process governance.

Automation may:
- create draft candidates
- enqueue schedules
- trigger reminders
- propose follow-ups
- emit non-final signals

Automation may not:
- silently finalize governed artifacts
- bypass AEGIS
- bypass Sentinel validation
- overwrite canonical records directly

All automation outputs re-enter the ProcessRecord pipeline.
