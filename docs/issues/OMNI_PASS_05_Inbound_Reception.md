# TITAN OMNI — PASS 05: Inbound Reception Pipeline

**Labels:** `titan-omni` `pass-05` `inbound` `reception` `webhooks` `do-not-activate`
**Milestone:** Titan Omni Implementation
**Depends on:** Pass 04 complete

---

## Global Instruction

Re-read all prior pass outputs before building. Thin controllers — all logic in services. Preserve unmatched identities. Do not force bad contact merges.

**Doctrine:** reuse → extend → refactor → repair → replace only if unavoidable.

---

## Goal

Implement the Omni reception engine end-to-end — from raw inbound webhook to persisted, classified conversation with audit trail.

---

## Read First — Docs

- `docs/titan-omni/reception-engine/TITAN_OMNI_RECEPTION_ENGINE.md`
- `docs/titan-omni/services/TITAN_OMNI_SERVICE_BINDINGS.md`
- `docs/titan-omni/controllers/TITAN_OMNI_CONTROLLER_MAP.md`
- `docs/titan-omni/integration-surface/TITAN_OMNI_INTEGRATION_SURFACE.md`
- `docs/titan-omni/channel-drivers/TITAN_OMNI_DRIVER_IMPLEMENTATION_REPORT.md` (Pass 04 output)

---

## Read First — CodeToUse

- `CodeToUse/TitanTalk*` — inbound routing / identity resolution
- `CodeToUse/comms*` — webhook reception / inbound normalisation
- `CodeToUse/utilities*` — any inbound routing / webhook utilities
- `CodeToUse/MarketingBot*` — inbound response logic if present

---

## Build — Reception Service Pipeline

`app/Omni/Services/Reception/InboundReceptionService.php`

Steps (in order):
1. Receive raw payload from webhook endpoint
2. Resolve channel driver via `ChannelDriverRegistry`
3. Normalise inbound event via driver `normalise()`
4. Resolve identity via driver `resolveIdentity()` + `IdentityResolverService`
5. Match/create identity evidence (phone, email, external ID)
6. Match contact cautiously — low-confidence stays unlinked, not forced
7. Create or find conversation (thread matching logic)
8. Attach host object when identity evidence is confirmed (Customer, Lead, Job)
9. Classify intent — rules first, AI hook available but not required
10. Decide destination — operator inbox, automation queue, or review queue
11. Persist audit trail entry for every step

### Supporting Services
- `app/Omni/Services/Identity/IdentityResolverService.php`
  - `resolve(NormalisedPayload $payload): IdentityCandidate`
  - `findOrCreateEvidence(IdentityCandidate $candidate): IdentityEvidence`
  - `matchContact(IdentityEvidence $evidence, int $companyId): ?Contact`
- `app/Omni/Services/Conversation/ConversationMatcherService.php`
  - `findOrCreate(IdentityEvidence $evidence, string $channel, int $companyId): Conversation`
  - `attachHostObject(Conversation $conversation, Model $subject): void`
- `app/Omni/Services/Intent/IntentClassifierService.php`
  - `classify(Message $message, Conversation $conversation): IntentResult`
  - Rules-based classification first; AI hook for unclassified

### Webhook Controllers (thin)
- `app/Http/Controllers/Omni/Inbound/SmsInboundController.php`
- `app/Http/Controllers/Omni/Inbound/EmailInboundController.php`
- `app/Http/Controllers/Omni/Inbound/WhatsAppInboundController.php`
- `app/Http/Controllers/Omni/Inbound/VoiceInboundController.php`
- `app/Http/Controllers/Omni/Inbound/WebChatInboundController.php`
- Each: receive → validate signature → dispatch to `InboundReceptionService` → return 200

### Routes
- New file `routes/omni/inbound.php` — public webhook routes (no auth, but signed)
- Load from `RouteServiceProvider` or `TitanOmniServiceProvider`

### Events / Jobs
- `app/Events/Omni/MessageReceived.php`
- `app/Events/Omni/ConversationCreated.php`
- `app/Events/Omni/IdentityUnresolved.php` — fires when contact match fails
- `app/Jobs/Omni/ProcessInboundMessage.php` — queued processing

---

## Rules

- Preserve unmatched identities — they must remain reviewable
- Do not force bad contact merges — low-confidence stays pending
- Controllers must be thin: receive, validate, dispatch — nothing else
- Every step of the pipeline must produce an audit trail record
- Failed reception must not throw unhandled exceptions — log and persist error state

---

## Required Output

- Inbound controllers in `app/Http/Controllers/Omni/Inbound/`
- Services in `app/Omni/Services/Reception/`, `Identity/`, `Conversation/`, `Intent/`
- Routes in `routes/omni/inbound.php`
- Events and jobs
- `docs/titan-omni/reception-engine/TITAN_OMNI_RECEPTION_IMPLEMENTATION_REPORT.md`

---

## Pass Delivery Rules

1. What was scanned
2. What donor code was reused
3. What host code was extended
4. What files were created
5. What docs were updated
6. What remains for Pass 06
