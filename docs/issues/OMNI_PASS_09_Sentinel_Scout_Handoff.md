# TITAN OMNI — PASS 09: Sentinel / Scout + Handoff + Overlays

**Labels:** `titan-omni` `pass-09` `sentinel` `scout` `handoff` `overlays` `do-not-activate`
**Milestone:** Titan Omni Implementation
**Depends on:** Pass 08 complete

---

## Global Instruction

Re-read all prior pass outputs before building. Sentinel = triage/routing/governance. Scout = action/follow-up/capture assistance. Overlays must modify behaviour, not create a parallel stack. All decisions must be auditable.

**Doctrine:** reuse → extend → refactor → repair → replace only if unavoidable.

---

## Goal

Implement the decision and specialisation layer — Sentinel routing rules, Scout tools, handoff rules, escalation thresholds, and overlay bindings.

---

## Read First — Docs

- `docs/titan-omni/sentinel-scout/TITAN_OMNI_SENTINEL_SCOUT.md`
- `docs/titan-omni/overlay-runtime/TITAN_OMNI_OVERLAY_RUNTIME.md`
- `docs/titan-omni/configs/TITAN_OMNI_CONFIG_SURFACE.md`
- `docs/titan-omni/alignment/TITAN_OMNI_COMMS_ALIGNMENT.md`
- All pass reports from 01–08 (re-read before building)

---

## Read First — CodeToUse

- `CodeToUse/MarketingBot*` — donor routing / response script logic
- `CodeToUse/comms*` — donor routing / workflow logic
- `CodeToUse/TitanTalk*` — donor call/reception logic, script overlays
- Any overlay-related donor material in docs and supporting code

---

## Build

### Sentinel — Triage / Routing / Governance
- `app/Omni/Services/Sentinel/SentinelRoutingService.php`
  - `evaluate(Conversation $conversation, Message $message): RoutingDecision`
  - `applyRules(Conversation $conversation): RoutingDecision`
  - `shouldEscalate(Conversation $conversation): bool`
  - `shouldAutoResolve(Conversation $conversation): bool`
  - `getActiveOverlays(Conversation $conversation): Collection`
- `app/Omni/Services/Sentinel/SentinelRuleEngine.php`
  - Rules evaluated in priority order
  - Rule types: keyword match, intent match, channel, contact segment, time-of-day, SLA breach
  - Each rule produces a `RoutingDecision`: route-to-operator | route-to-queue | route-to-scout | auto-reply | escalate

### Scout — Action / Follow-up / Capture
- `app/Omni/Services/Scout/ScoutActionService.php`
  - `triggerFollowUp(Conversation $conversation, string $reason): void`
  - `captureLeadFromConversation(Conversation $conversation): ?Lead`
  - `suggestReply(Conversation $conversation): string` — AI-assisted, optional hook
  - `scheduleCallback(Conversation $conversation, Carbon $at): void`
- Scout actions are suggestions/assists — operator can accept, modify, or dismiss

### Handoff Rules
- `app/Omni/Services/Handoff/HandoffService.php`
  - `evaluate(Conversation $conversation): ?HandoffRule`
  - `execute(Conversation $conversation, HandoffRule $rule): void`
  - `transferToOperator(Conversation $conversation, ?User $operator = null): void`
  - `transferToQueue(Conversation $conversation, string $queue): void`
  - Handoff state visible to operator at all times

### Escalation
- `app/Omni/Services/Sentinel/EscalationService.php`
  - `escalate(Conversation $conversation, string $reason): void`
  - `getEscalationThresholds(int $companyId): array`
  - Escalation creates alert + assigns to supervisor queue

### Overlays
- `app/Omni/Services/Overlay/OverlayRuntimeService.php`
  - `getActiveOverlays(Conversation $conversation): Collection`
  - `applyOverlay(Conversation $conversation, OverlayBinding $overlay): void`
  - Overlay types: prompt modifier, cadence modifier, compliance gate, channel restriction
  - Overlays bind to: channel, contact segment, intent, host object type, time window

### Admin / Editor UI (basic)
- `resources/views/default/panel/user/omni/sentinel/rules.blade.php` — rule list + editor
- `resources/views/default/panel/user/omni/sentinel/overlays.blade.php` — overlay bindings
- `resources/views/default/panel/user/omni/sentinel/handoffs.blade.php` — handoff rule config
- `app/Http/Controllers/Omni/SentinelController.php`

### Review Tools for Low-Confidence Routes
- `resources/views/default/panel/user/omni/review/routing.blade.php`
- Conversations where Sentinel confidence was below threshold
- Operator can confirm routing, reassign, or dismiss

---

## Rules

- Sentinel = triage/routing/governance only — no sending, no CRM mutations
- Scout = action/assist only — operator retains final control on all scout suggestions
- Overlays must modify existing behaviour through hooks, not replace infrastructure
- Every routing decision must be persisted to the audit trail with rule ID and confidence score
- Escalation must create a visible alert — never silently escalate
- Handoff state must always be visible to the operator in the thread view

---

## Required Output

- Services in `app/Omni/Services/Sentinel/`, `Scout/`, `Handoff/`, `Overlay/`
- Views in `resources/views/default/panel/user/omni/sentinel/` and `review/`
- Controllers in `app/Http/Controllers/Omni/`
- `docs/titan-omni/sentinel-scout/TITAN_OMNI_SENTINEL_SCOUT_IMPLEMENTATION_REPORT.md`
- `docs/titan-omni/overlay-runtime/TITAN_OMNI_OVERLAY_IMPLEMENTATION_REPORT.md`

---

## Pass Delivery Rules

1. What was scanned
2. What donor routing/overlay code was reused
3. What host code was extended
4. What files were created
5. What docs were updated
6. What remains for Pass 10
