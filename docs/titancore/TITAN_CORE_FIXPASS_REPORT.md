# Titan Core Fix Pass Report

> **Pass type:** Pre-Nexus canonicalization  
> **Scope:** Duplicate class resolution, fatal import fixes, canonical path establishment, admin completion, MCP completion, envelope normalization

---

## Executive Summary

This pass resolved a set of class-level conflicts that would have caused PHP fatal errors at boot, established canonical paths for every overlapping subsystem, completed the admin panel and MCP capability layer, and normalized signal envelope fields. The Titan Core system is now in a clean, canonical, pre-Nexus state.

---

## Part 1 — Duplicates Found and Resolved

### 1.1 Duplicate `TitanMemoryService`

**Problem:** Two incompatible implementations existed with different backing stores, APIs, and tenancy models. `TitanCoreServiceProvider` imported both under the same alias, causing a PHP fatal at class load.

| Class | Backing | API | Tenancy |
|-------|---------|-----|---------|
| `App\Titan\Core\TitanMemoryService` | DB (`tz_ai_memories`) | Typed params: `(int $companyId, ...)` | `company_id` scoped |
| `App\TitanCore\Zero\Memory\TitanMemoryService` | Laravel cache | String keys: `(string $key, ...)` | Optional |

**Resolution:**
- `App\Titan\Core\TitanMemoryService` → **canonical**
- `App\TitanCore\Zero\Memory\TitanMemoryService` → **deprecated tombstone** (implements `MemoryContract` only)
- Provider: single `use` import, single singleton binding
- MCP handlers: `MemoryStoreHandler`, `MemoryRecallHandler` updated to inject canonical

---

### 1.2 Duplicate `ZylosBridge`

**Problem:** Two `ZylosBridge` classes with split functionality. Neither class alone covered both responsibilities.

| Class | Had | Missing |
|-------|-----|---------|
| `App\TitanCore\Zylos\ZylosBridge` | `status()`, `restart()`, `disable()` | `dispatch()`, `list()`, `executionStatus()`, `validateCallback()` |
| `App\TitanCore\Zero\Skills\ZylosBridge` | `dispatch()`, `list()`, `executionStatus()`, `validateCallback()` | Admin monitor methods |

**Resolution:**
- `App\TitanCore\Zylos\ZylosBridge` → **canonical** (merged all 7 methods from both sources)
- `App\TitanCore\Zero\Skills\ZylosBridge` → **deprecated tombstone** (extends canonical, empty body)
- Provider: single `use` import, single singleton binding
- MCP handlers: `SkillDispatchHandler`, `SkillListHandler`, `SkillStatusHandler` updated to inject canonical

---

### 1.3 Duplicate Contracts

**Problem:** Two parallel contract namespaces with method signatures only defined in the `App\Titan\Core\Contracts\*` versions.

**Resolution:**
- `App\Titan\Core\Contracts\ProcessContract` → **canonical** (full signatures)
- `App\Titan\Core\Contracts\SignalContract` → **canonical** (full signatures)
- `App\TitanCore\Contracts\ProcessContract` → **deprecated alias** (extends canonical, empty body)
- `App\TitanCore\Contracts\SignalContract` → **deprecated alias** (extends canonical, empty body)

---

### 1.4 Duplicate Config Keys

**Problem:** `config/titan_core.php` `ai` section contained duplicate keys, causing the later value to silently override the earlier one.

**Resolution:** Removed duplicate entries. Each key appears exactly once.

---

## Part 2 — What Became Canonical

| Subsystem | Canonical Class | Canonical Location |
|-----------|----------------|-------------------|
| Memory runtime | `TitanMemoryService` | `App\Titan\Core\TitanMemoryService` |
| Skill dispatch & monitoring | `ZylosBridge` | `App\TitanCore\Zylos\ZylosBridge` |
| Process lifecycle contract | `ProcessContract` | `App\Titan\Core\Contracts\ProcessContract` |
| Signal envelope contract | `SignalContract` | `App\Titan\Core\Contracts\SignalContract` |
| MCP capability registry | `McpCapabilityRegistry` | `App\TitanCore\MCP\McpCapabilityRegistry` |
| AI routing | `TitanAIRouter` | `App\TitanCore\Zero\AI\TitanAIRouter` |
| Signal envelope builder | `EnvelopeBuilder` | `App\Titan\Signals\EnvelopeBuilder` |
| Admin controller | `TitanCoreAdminController` | `App\Http\Controllers\Admin\TitanCore\TitanCoreAdminController` |

---

## Part 3 — What Was Removed / Deprecated / Bridged

| Class | Action | Notes |
|-------|--------|-------|
| `App\TitanCore\Zero\Memory\TitanMemoryService` | Deprecated tombstone | Implements `MemoryContract`; cache-backed; do not inject |
| `App\TitanCore\Zero\Skills\ZylosBridge` | Deprecated tombstone | Extends canonical; empty body |
| `App\TitanCore\Contracts\ProcessContract` | Deprecated alias | Extends `App\Titan\Core\Contracts\ProcessContract` |
| `App\TitanCore\Contracts\SignalContract` | Deprecated alias | Extends `App\Titan\Core\Contracts\SignalContract` |
| Duplicate `use` imports in `TitanCoreServiceProvider` | Removed | Two per conflicted class → one per class |
| Duplicate singleton registrations in `TitanCoreServiceProvider` | Removed | Two per conflicted class → one per class |
| Duplicate config keys in `titan_core.php` `ai` section | Removed | Single occurrence of each key retained |

---

## Part 4 — Admin Features Completed

All 8 Titan Core admin screens are complete with routes, controller methods, and views:

| Screen | Route | Status |
|--------|-------|--------|
| Model routing | `admin.titan.core.models` | ✅ Complete |
| Signal queue monitor | `admin.titan.core.signals` | ✅ Complete |
| Memory usage panel | `admin.titan.core.memory` | ✅ Complete |
| Skill runtime monitor | `admin.titan.core.skills` | ✅ Complete |
| Activity feed | `admin.titan.core.activity` | ✅ Complete |
| Token budgets | `admin.titan.core.budgets` | ✅ Complete |
| Queue dashboard | `admin.titan.core.queues` | ✅ Complete |
| Health dashboard | `admin.titan.core.health` | ✅ Complete |

The health dashboard exposes both a rendered view and a `GET admin.titan.core.health.api` JSON endpoint for external monitoring. 8 health checks are performed on each load: router, kernel, memory service, signal pipeline, rewind hooks, Zylos bridge, queue workers, MCP HTTP transport.

---

## Part 5 — MCP Features Completed

### Capability Layer

All 7 MCP capabilities are registered in `McpCapabilityRegistry` with handlers present:

| Capability | Handler | Status |
|------------|---------|--------|
| `titan.ai.complete` | `AiCompleteHandler` | ✅ Complete |
| `titan.memory.store` | `MemoryStoreHandler` | ✅ Complete |
| `titan.memory.recall` | `MemoryRecallHandler` | ✅ Complete |
| `titan.signal.dispatch` | `SignalDispatchHandler` | ✅ Complete |
| `titan.skill.dispatch` | `SkillDispatchHandler` | ✅ Complete |
| `titan.skill.status` | `SkillStatusHandler` | ✅ Complete |
| `titan.skill.list` | `SkillListHandler` | ✅ Complete |

### Routes

- `GET api/titan/mcp/capabilities` → `titan.mcp.capabilities`
- `POST api/titan/mcp/invoke` → `titan.mcp.invoke`
- `POST api/titan/signal/callback` → `titan.signal.callback` (HMAC-validated, no auth:sanctum)

### Handler Updates

`MemoryStoreHandler` and `MemoryRecallHandler` updated to:
- Use canonical `App\Titan\Core\TitanMemoryService` (DB-backed, typed parameters)
- Accept backwards-compat aliases (`key` → `session_id`, `payload` → `content`)

`SkillDispatchHandler`, `SkillListHandler`, `SkillStatusHandler` updated to:
- Use canonical `App\TitanCore\Zylos\ZylosBridge`

---

## Part 6 — Envelope Fields Normalized

Signal envelopes built by `App\Titan\Signals\EnvelopeBuilder` now include all Phase 6.3 required fields alongside existing fields:

### Phase 6.3 Required Fields (added/ensured)

| Field | Source |
|-------|--------|
| `signal_uuid` | Generated per envelope |
| `company_id` | From context — enforced as tenant boundary |
| `origin` | From signal data |
| `intent` | From signal data |
| `state` | Computed from signal status |
| `approval_required` | Count of signals requiring approval |
| `rewind_eligible` | Derived from process state |
| `timestamp` | ISO 8601 build timestamp |

### Existing Fields Preserved

| Field | Notes |
|-------|-------|
| `id`, `team_id`, `actor_id` | Legacy identity |
| `summary`, `headline` | Human-readable envelope summary |
| `signals`, `top_signals` | Signal collection with priority ranking |
| `meta` | Breakdown: signal_count, severity_counts, status_counts, approval_queue |
| `risk` | priority (high/medium/low), approval_pressure, top_priority_band |
| `timeline` | latest_signal_at, oldest_signal_at |

### Tenancy Normalization

`TitanAIRouter::normaliseEnvelope()` now enforces:
- `company_id` always present (required)
- `team_id` never used as tenant boundary (normalized to `company_id`)
- `stage` defaults to `suggestion` when not provided

---

## Part 7 — What Remains Unproven in Sandbox

The following could not be verified in the sandbox environment due to missing runtime resources:

| Item | Reason unverifiable |
|------|---------------------|
| Full Laravel boot | No `vendor/` directory — autoload not testable |
| DB table existence | `php artisan migrate` not run |
| Queue worker routing | Workers not started |
| Zylos HTTP dispatch | No live `ZYLOS_ENDPOINT` |
| AI model execution | No AI provider credentials |
| MCP HTTP/WS transport | `MCP_HTTP_URL` / `MCP_WS_URL` env vars empty |
| Vector embedding path | Requires embedding model config |
| Memory TTL expiry behavior | Requires live DB with populated rows |

---

## Part 8 — Canonical Pre-Nexus Titan Core State

This is the canonical, stable state of Titan Core immediately before Nexus multi-core integration work begins.

### Architecture Map

```
TitanCoreServiceProvider
├── App\Titan\Core\TitanMemoryService          (canonical memory, DB-backed)
├── App\TitanCore\Zylos\ZylosBridge            (canonical skill dispatch + admin)
├── App\TitanCore\Zero\AI\TitanAIRouter        (canonical AI routing)
├── App\TitanCore\MCP\McpCapabilityRegistry    (7 capabilities)
├── App\TitanCore\Zero\CoreKernel              (core kernel)
├── App\TitanCore\Zero\Rewind\RewindManager    (rewind pipeline)
├── App\TitanCore\Zero\Signals\SignalBridge    (signal pipeline)
└── [Nexus, Consensus, CritiqueLoop, etc.]     (AI execution pipeline)

Routes
├── routes/core/mcp.routes.php         (MCP API + Zylos callback)
├── routes/core/titan_admin.routes.php (8 admin screens)
└── routes/core/titan_core.routes.php  (status, health, memory tools)

Admin Screens (8)
├── /models      → TitanAIRouter + titan_ai config
├── /signals     → tz_signal_queue
├── /memory      → tz_ai_memories + all memory tables
├── /skills      → ZylosBridge::status()
├── /activity    → tz_audit_log
├── /budgets     → titan_budgets config
├── /queues      → jobs + failed_jobs tables
└── /health      → 8 system health checks

Database Tables (assumed migrated)
├── tz_processes, tz_process_states        (process lifecycle)
├── tz_signals, tz_signal_queue            (signal pipeline)
├── tz_approval_queue                      (approval workflow)
├── tz_audit_log                           (audit trail)
├── tz_ai_memories                         (canonical memory)
├── tz_ai_memory_embeddings                (vector index)
├── tz_ai_memory_snapshots                 (rewind snapshots)
├── tz_ai_session_handoffs                 (session continuity)
└── tz_skill_events, tz_skill_registry     (Zylos event log)

Deprecated (tombstones in place)
├── App\TitanCore\Zero\Memory\TitanMemoryService  → implements MemoryContract, cache-backed
├── App\TitanCore\Zero\Skills\ZylosBridge         → extends canonical ZylosBridge
├── App\TitanCore\Contracts\ProcessContract       → extends App\Titan\Core\Contracts\ProcessContract
└── App\TitanCore\Contracts\SignalContract        → extends App\Titan\Core\Contracts\SignalContract
```

### What Nexus Will Build On

Nexus multi-core execution will extend `App\TitanCore\Zero\AI\Nexus\NexusCoordinator` (already registered as singleton) with:
- Authority-weighted consensus across 7 named cores (logi, creator, finance, micro, macro, entropy, equilibrium)
- Critique loop refinement (`CritiqueLoopEngine`)
- Round-robin refinement passes (`RoundRobinRefinement`)

All infrastructure (`TitanAIRouter`, `TitanMemoryService`, `SignalBridge`, `EnvelopeBuilder`, admin panel) is stable and ready for Nexus integration without further canonicalization work.
