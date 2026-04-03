# TITAN CORE EXECUTION PIPELINE

**Version:** Prompt 6  
**Status:** Production-Ready Foundation

---

## Overview

The Titan Core execution pipeline is a single-authority routing system. Every AI completion, memory operation, signal dispatch, and skill execution MUST route through the canonical entry points defined here. No subsystem may call a provider SDK directly.

---

## Canonical Execution Path

```
Caller (MCP / Controller / Omni Surface)
    │
    ▼
TitanAIRouter::route()   ← sole gateway for AI completions
    │
    ├─ TitanTokenBudget::isAllowed()     ← budget gate (blocks if exceeded)
    │
    ├─ ZeroCoreManager::decide()
    │      ├─ MemoryManager::snapshot()
    │      ├─ KnowledgeManager::resolve()
    │      ├─ DecisionContextFactory::make()
    │      ├─ InstructionBuilder::build()
    │      ├─ RuntimeManager::adapter()::execute()
    │      ├─ ConsensusCoordinator::resolve()
    │      └─ NexusCoordinator::evaluate()
    │
    ├─ TitanTokenBudget::record()        ← usage tracking
    │
    ├─ SignalBridge::recordAndPublish()   ← state signal emission
    │
    └─ event(TitanCoreActivity)          ← telemetry (titan.core.activity)
```

---

## Signal Dispatch Path

```
SignalsService::recordAndIngest()
    │
    ├─ EnvelopeBuilder::build()          ← Phase 6.3 compliant fields
    │
    └─ SignalDispatcher::dispatch()
           ├─ ZeroSubscriber::handle()
           ├─ PulseSubscriber::handle()
           └─ RewindSubscriber::handle()
```

---

## Skill Execution Path (Async)

```
ZylosBridge::dispatch()
    │
    ├─ HMAC-SHA256 signature (X-Zylos-Signature)
    │
    ├─ POST {ZYLOS_ENDPOINT}/dispatch    ← external skill runtime
    │
    └─ Signed callback → POST /api/titan/signal/callback
           └─ ValidateZylosSignature middleware
```

---

## Memory Operations

```
TitanMemoryService (implements MemoryContract)
    ├─ store(key, payload, company_id)   → Cache::put (TTL from TITAN_MEMORY_TTL)
    ├─ recall(key, company_id)           → Cache::get
    ├─ snapshot(key)                     → MemoryManager::snapshot
    └─ expire(key, company_id)           → Cache::forget
```

---

## MCP Invocation Path

```
POST /api/titan/mcp/invoke
    │
    ├─ auth:sanctum                      ← no anonymous access
    ├─ EnforceTitanTenancy               ← company_id required
    ├─ throttle:60,1
    │
    └─ McpCapabilityRegistry::get(name)
           └─ Handler::handle(params)
                  └─ delegates to canonical service
```

---

## Execution Authority Boundaries

| Component | Authority | May call |
|-----------|-----------|----------|
| TitanAIRouter | AI completion | ZeroCoreManager, SignalBridge, TitanTokenBudget |
| ZeroCoreManager | Decision | RuntimeManager, NexusCoordinator, MemoryManager |
| RuntimeManager | Provider selection | RuntimeAdapterContract implementations |
| ZylosBridge | Skill dispatch | External Zylos endpoint only |
| TitanMemoryService | Memory I/O | Cache driver only |
| SignalDispatcher | Signal fan-out | Registered subscribers only |

**Forbidden patterns:**
- `OpenAI::*` direct calls
- `DeepSeek::*` direct calls
- `FalAI::*` direct calls
- `ElevenLabs::*` direct calls
- `$client->chat()` direct calls
- `$client->generate()` direct calls

All provider calls MUST route through `TitanAIRouter::route()`.

---

## Router Guarantees

1. **Single gateway** — `route()` and `execute()` are aliases; no other entry point bypasses budget/telemetry.
2. **Budget enforced before execution** — `TitanTokenBudget::isAllowed()` is called before `ZeroCoreManager::decide()`.
3. **Telemetry always fires** — `TitanCoreActivity` event is emitted on both success and budget-block.
4. **Signal always published** — `SignalBridge::recordAndPublish()` fires after every successful execution.
5. **Tenancy always present** — `company_id` is normalised from `team_id` fallback in `normaliseEnvelope()`.
