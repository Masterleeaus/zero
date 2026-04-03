# TITAN OMNI — PASS 10: Titan Zero Integration + Hardening

**Labels:** `titan-omni` `pass-10` `integration` `hardening` `final` `do-not-activate`
**Milestone:** Titan Omni Implementation
**Depends on:** Passes 01–09 complete

---

## Global Instruction

Re-read ALL pass implementation reports (01–09) before building anything in this pass. This is the integration and hardening pass — no new subsystems. Fix gaps, wire surfaces, harden config, polish audit.

**Doctrine:** reuse → extend → refactor → repair → replace only if unavoidable.

---

## Goal

Finish Omni as a fully host-integrated subsystem. Wire Omni into Titan Zero's signal layer, object-link helpers, assistant tools, config system, and audit infrastructure.

---

## Read First — Docs (mandatory before starting)

- `docs/titan-omni/integration-surface/TITAN_OMNI_INTEGRATION_SURFACE.md`
- `docs/titan-omni/alignment/TITAN_OMNI_ZERO_ALIGNMENT.md`
- `docs/titan-omni/source-maps/TITAN_OMNI_PROVIDER_MAP.md`
- `docs/titan-omni/source-maps/TITAN_OMNI_ROUTE_MAP.md`
- ALL implementation reports from passes 01–09:
  - `docs/titan-omni/source-maps/TITAN_OMNI_HOST_INSERTION_MAP.md`
  - `docs/titan-omni/migrations/TITAN_OMNI_MIGRATION_IMPLEMENTATION_REPORT.md`
  - `docs/titan-omni/models/TITAN_OMNI_MODEL_IMPLEMENTATION_REPORT.md`
  - `docs/titan-omni/channel-drivers/TITAN_OMNI_DRIVER_IMPLEMENTATION_REPORT.md`
  - `docs/titan-omni/reception-engine/TITAN_OMNI_RECEPTION_IMPLEMENTATION_REPORT.md`
  - `docs/titan-omni/conversation-graph/TITAN_OMNI_UI_IMPLEMENTATION_REPORT.md`
  - `docs/titan-omni/channel-drivers/TITAN_OMNI_OUTBOUND_IMPLEMENTATION_REPORT.md`
  - `docs/titan-omni/campaign-runtime/TITAN_OMNI_SEQUENCE_IMPLEMENTATION_REPORT.md`
  - `docs/titan-omni/sentinel-scout/TITAN_OMNI_SENTINEL_SCOUT_IMPLEMENTATION_REPORT.md`
  - `docs/titan-omni/overlay-runtime/TITAN_OMNI_OVERLAY_IMPLEMENTATION_REPORT.md`

---

## Read First — CodeToUse

- Any signal/orchestration donor code
- Existing Titan Zero integration surfaces in host (`app/Titan/Signals/`, `app/Extensions/TitanRewind/`)
- `CodeToUse/utilities*` — events, queues, logging, audit, retries

---

## Build

### Titan Signal Integration
- Hook Omni events into `app/Titan/Signals/SignalDispatcher.php`
- Emit signals for: `omni.message_received`, `omni.conversation_created`, `omni.identity_unresolved`, `omni.delivery_failed`, `omni.sequence_started`, `omni.escalation_triggered`, `omni.handoff_executed`
- `app/Omni/Listeners/EmitOmniSignal.php` — generic signal emitter listening to all Omni events
- Register in `app/Providers/EventServiceProvider.php`

### Object-Link Helpers
- `app/Omni/Support/OmniObjectLinker.php`
  - `linkToCustomer(Conversation $conversation, Customer $customer): void`
  - `linkToJob(Conversation $conversation, ServiceJob $job): void`
  - `linkToQuote(Conversation $conversation, Quote $quote): void`
  - `linkToLead(Conversation $conversation, Lead $lead): void`
  - `getLinkedObjects(Conversation $conversation): array`
- Surface links in conversation thread view sidebar (Pass 06 views)

### Assistant Tool Exposure Points
- `app/Omni/Tools/OmniConversationTool.php` — exposes conversation context to AI assistant
  - `getRecentMessages(Conversation $conversation, int $limit = 10): array`
  - `getSummary(Conversation $conversation): string`
  - `getContactProfile(Conversation $conversation): array`
- Wire to existing Entity driver abstraction (`app/Domains/Entity/Drivers/`) for AI calls

### Config Hardening
- Review `config/titan-omni.php` for completeness
- Add: rate limits per channel, max sequence steps, escalation thresholds, review queue SLA, attachment MIME allowlist
- All sensitive values must be env-driven with safe defaults

### Permissions
- Gate all Omni routes behind appropriate permissions
- Add Omni permission keys to host permissions/policy system
- Operator vs Admin vs Supervisor permission separation

### Audit / Logging Polish
- Verify every pipeline step writes to audit trail
- Add structured logging (`Log::channel('omni')`) for all service operations
- Failed delivery recovery: surface in UI + provide retry action
- Replay/retry visibility: operators can see all failed deliveries and trigger retry

### Final Integrity Checks
Run through the following and fix any gaps found:

1. No duplicate CRM ownership — Omni Contact links to host Customer, never replaces
2. No duplicate Jobs ownership — Omni links to `ServiceJob`, never owns it
3. No duplicate Finance ownership — Omni never touches invoice/payment tables directly
4. No tenant leaks — every query scoped by `company_id`
5. No broken routes — all Omni routes resolve correctly, no 404s
6. No broken providers — `TitanOmniServiceProvider` boots cleanly with missing credentials
7. No parallel comms stack left behind — remove any stub/duplicate implementations
8. Inbox usable — conversations visible, thread renders, send works
9. Reception usable — inbound webhooks accept, process, persist
10. Sequences usable — at least one sequence can be started and stepped

---

## Required Output — Final Docs

Create:
- `docs/titan-omni/TITAN_OMNI_FINAL_INTEGRATION_REPORT.md`
  - Summary of all 10 passes
  - What is fully working
  - What is stubbed/pending provider credentials
  - What requires operator configuration before use
- `docs/titan-omni/TITAN_OMNI_REPO_WIRING_MAP.md`
  - Every Omni service → host connection point documented
  - Signal emission points
  - Route files and prefixes
  - Config keys and env vars
- `docs/titan-omni/TITAN_OMNI_GAP_LIST_POST_PASS10.md`
  - Known gaps, stubs, and incomplete provider integrations
  - Classified: CRITICAL | HIGH | MEDIUM | LOW

---

## Pass Delivery Rules

1. What was scanned (all 9 pass reports + host integration surfaces)
2. What was wired or fixed
3. What host code was extended
4. What files were created/modified
5. All three final docs created
6. Gap list complete and classified
