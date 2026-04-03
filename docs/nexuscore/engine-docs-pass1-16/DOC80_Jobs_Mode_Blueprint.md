# Jobs Mode Blueprint

## Scope
Service lifecycle execution surface.

## Entities
Customer → Site → Job → Checklist → Evidence → Outcome → Follow‑up

## Responsibilities
- Job scheduling
- Route ordering
- Checklist execution
- Proof capture
- Exception logging
- Service outcomes
- Rebook triggers

## Signals
job.created
job.started
job.completed
job.failed
job.rebook.suggested
