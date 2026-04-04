# FAQ

**Q: Is AIPlatform production-ready?**  
A: Core features are ready; treat quantum modules as experimental. Follow `DEPLOYMENT.md` for production checklist.

**Q: Can I bring my own model provider?**  
A: Yes — implement the provider interface in `gen-ai-engine/providers/` and add configuration.

**Q: Does it store user data?**  
A: Yes — object storage for artifacts, DB for metadata. See `SECURITY.md` and `PRIVACY.md` (if added) for policies.

**Q: How do I contribute models?**  
A: Open a PR following `CONTRIBUTING.md`, include tests and example pipelines.



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
