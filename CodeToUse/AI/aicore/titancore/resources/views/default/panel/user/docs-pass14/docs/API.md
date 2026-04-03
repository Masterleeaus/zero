# API Reference (Overview)

This document gives a high-level overview. For auto-generated OpenAPI, see `docs/openapi.yaml` (or generate from code).

## Authentication
- All endpoints use Bearer tokens (JWT).
- Token exchange: `POST /auth/token` with client credentials or password flow.

## Main endpoints (examples)
- `GET /api/v1/models` — list available model adapters
- `POST /api/v1/pipelines/run` — run a GenAI pipeline (body: pipeline id + input)
- `GET /api/v1/jobs/{id}` — job status and logs
- `POST /api/v1/tenants` — create tenant (admin)

## Error handling
- Standard HTTP codes used (400/401/403/404/429/500)
- Error body:
```json
{ "error": { "code": "VALIDATION_ERROR", "message": "Validation failed", "details": [...] } }
```

## Rate limiting
- 100 requests/min by default (per API key). Quotas configurable.



---
## Titan Zero Signal-Orchestrated Runtime Layer

Runtime clarification for contributors:

• All state mutations MUST originate as signals
• Controllers should emit ProcessRecords, not direct writes
• Automation executes exclusively through Pulse handlers
• Corrections must route through Rewind workflows
• Devices act as primary execution nodes
• Server promotes canonical events only after validation
• Sync transports intent envelopes, never raw DB state

Recommended controller pattern:

Request → ProcessRecord → Signal → Validation → Event → Pulse → Domain Update



---
## Titan Zero Provider & Memory Isolation Model

Provider usage and memory access must follow the new node-first governance model.

Rules:

• Providers receive scoped context envelopes, never unrestricted tenant datasets
• BYO and tenant-scoped keys should remain isolated per company boundary
• Memory must attach to entities, lifecycle stages, or approved assistant scopes
• Cross-tenant retrieval is prohibited
• Node-local drafts, evidence, and short-term reasoning remain local until signal promotion requires transmission
• Assistants emit signals and reference scoped memory; they do not directly mutate entities

Preferred reasoning flow:

Node Context → Scoped Memory → Provider Envelope → Signal Proposal → Validation → Canonical Event
