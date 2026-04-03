# Deployment & Production Checklist

## Recommended infra
- Kubernetes (EKS/GKE/AKS) or Docker Compose for smaller installs
- Managed DB (Postgres) with backups
- Object store (S3-compatible)
- GPU nodes for model inference (if self-hosting heavy models)

## Environment variables
- Keep secrets in a secret manager (Vault / AWS Secrets Manager)
- Do not store secrets in `.env` in production.

## Build & release
1. Build containers (multi-arch if needed)
2. Run integration tests
3. Tag a release (SemVer)
4. Publish images to registry
5. Deploy via helm / k8s manifests or compose

## Scaling
- Horizontal scale API; vertical on GPU nodes for models
- Use job queue worker autoscaling for bursts

## Backups & DR
- DB backups daily
- Object store lifecycle rules
- Disaster Recovery plan tested quarterly



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
