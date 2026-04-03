# Titan Signal API Map

## Endpoints

- `POST /api/signals/ingest`
- `POST /api/signals/processes/record`
- `POST /api/signals/processes/record-and-ingest`
- `POST /api/signals/processes/{processId}/transition`
- `POST /api/signals/dispatch/pending`
- `GET /api/signals/approvals`
- `POST /api/signals/approvals/{processId}`
- `GET /api/signals/feed`
- `GET /api/signals/timeline/{processId}`
- `POST /api/signals/envelope`

## Canonical Fields

- `company_id`
- `team_id`
- `user_id`
- `process_id`
- `type`
- `kind`
- `severity`
- `payload`
- `meta`
- `status`
- `timestamp`

## Approval Decision Body

- `decision` = `approved|rejected`
- `actor` optional approver identifier
- `meta` optional structured notes
