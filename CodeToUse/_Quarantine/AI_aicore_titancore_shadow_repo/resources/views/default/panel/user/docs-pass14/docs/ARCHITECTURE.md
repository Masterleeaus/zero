# Architecture Overview — AIPlatform

## High-level
AIPlatform is modular, composed of:
- **Frontend (`app/`)**: landing + dashboard (SPA)
- **API / Backend (`ai-driven-core/`)**: authentication, tenancy, orchestration
- **Gen AI Engine (`gen-ai-engine/`)**: prompt management, pipelines, model adapters
- **Quantum Modules (`quantum-engineering/`)**: experimental accelerators & prototypes
- **Integrations**: git-systems, gitflic, storage adapters, real-time clients

## Data flows
1. User -> Frontend -> API
2. API invokes GenAI pipelines or delegates to adapters
3. Adapters call model providers or experimental quantum modules
4. Storage: object stores for artifacts; relational DB for metadata
5. Events: message bus (Redis / RabbitMQ) for async jobs

## Deployment topology
- Stateless frontends behind a load balancer
- Autoscaled backends (horizontal)
- Worker pool for gen-ai tasks (GPU nodes recommended)
- Optional quantum accelerator nodes (experimental)

## Security & multi-tenancy
- Per-tenant DB schemas or row-level isolation (configurable)
- JWT + OAuth2 for auth
- Rate limiting and quota enforcement in API gateway

## Observability
- Metrics: Prometheus
- Traces: Jaeger / OpenTelemetry
- Logs: centralized ELK / Loki

## Extensibility
- Adapters follow an interface: `adapter.register()` + async call patterns
- Add new model providers by implementing the provider interface in `gen-ai-engine/providers/`



---
## Titan Zero Federated Node Adjustment

This project now operates under a **device‑first federated node runtime model**:

• Devices act as primary execution nodes  
• Server coordinates validation, promotion, audit, and relay only  
• State changes must originate as signals before canonical promotion  
• Automation executes through Pulse handlers only  
• Corrections occur via Rewind — never destructive mutation  
• Tenant boundary enforced by `company_id`  
• Sync exchanges intent envelopes, not database state

Execution spine:

Device → Process → Signal → Validation → Canonical Event → Pulse → Rewind → Domain Update




---
## Titan Zero Lifecycle & Sync Enforcement Model

Lifecycle transitions MUST be signal-driven.

Rules:

• Lifecycle stages change only after canonical event promotion
• Offline node transitions remain draft until validated
• Pulse handlers execute post-promotion only
• Rewind governs corrections across lifecycle boundaries
• Sync transmits intent envelopes, never entity mutations

Lifecycle execution chain:

Assistant/User → ProcessRecord → Signal → Validation → Canonical Event → Pulse → Lifecycle Update



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
