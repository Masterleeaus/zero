# Pass 11 - Lifecycle Binding + Persona Routing

## What changed
- Added lifecycle-stage resolver for voice intents.
- Added dedicated handlers for quote, invoice, technician assignment, job close, and schedule changes.
- Upgraded persona routing to consider channel, lifecycle stage, intent, and conversation name hints.
- Upgraded unified command flow to prompt for missing entities before confirmation or fallback.
- Logged lifecycle stage into voice command audit data.

## New lifecycle intents
- create_quote
- create_invoice
- assign_technician
- close_job
- update_schedule

## Persona routing outcome
- Titan Nexus: support, quote, follow-up
- Titan Command: planning, dispatch, invoice
- Titan Go: dispatch, completion, field-work

## Recommended next pass
- Add persistent site/customer/job memory tables and session resolver.
- Bind handlers to real Worksuite services instead of response-only action stubs.
