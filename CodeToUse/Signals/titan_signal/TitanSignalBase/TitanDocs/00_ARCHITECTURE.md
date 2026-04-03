# Titan Signal Architecture

Titan Signal is the governed sensing layer for Titan Zero. It now uses `company_id` as the tenant boundary, preserves `team_id` as the crew layer, and keeps `user_id` as the actor identity.

## Core flow

1. Process is recorded into `tz_processes`.
2. Signal is normalized into the canonical `tz_signals` schema.
3. Feed and envelope surfaces provide Zero-ready views.
4. Next pass adds dispatcher fanout and richer validators.

## Current primitives

- `Signal` canonical object
- `SignalNormalizer`
- `SignalsService`
- `ProcessRecorder`
- `ProcessStateMachine`
- Work and Money providers
