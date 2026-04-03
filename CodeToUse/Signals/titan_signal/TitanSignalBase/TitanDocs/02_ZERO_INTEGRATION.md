# Zero Integration

Zero should consume envelopes with:

- `company_id`
- `team_id`
- `actor_id`
- `signals[]`
- `summary`
- `meta`

Signals are stored first, then surfaced through feed and envelope APIs. This keeps Zero aligned with the process-first model from the Signal & Processing Engine specification.
