# TITAN CORE PHASE PLAN

Generated: Prompt 1 — Implementation Sequencing

---

## Overview

TitanZero Core is built in 6 implementation prompts following this classification pass (Prompt 1).

The Zero Core Runtime Diagram defines the execution order:

```
User/Agent/Channel → Titan Omni → AIRouter → Memory + Signals
→ Pulse Automations → Approval Engine → Execution → Rewind Snapshot
```

Prompts are sequenced to build this stack bottom-up.

---

## Prompt 1 — Source Extraction and Architecture Scan ✅ (This Pass)

**Deliverables:**
- ✅ `zero_core.zip` extracted to `CodeToUse/aicore/titancore/`
- ✅ `AICores.zip` extracted to `CodeToUse/aicore/AICores/`
- ✅ `docs/titancore/` scanned
- ✅ Host ownership frozen
- ✅ Source map produced
- ✅ Duplication risks documented
- ✅ Phase plan written
- ✅ Minimal directory anchors created (`app/Titan/Core/`)
- ✅ Laravel app boots cleanly (no new code merged)

**App State:** Unchanged. No new production code.

---

## Prompt 2 — TitanCore Foundation and AIRouter Bootstrap

**Goal:** Import `app/TitanCore/` tree, register `TitanCoreServiceProvider`, wire `TitanAIRouter` as a singleton.

**Tasks:**
1. Copy `app/TitanCore/` from `CodeToUse/aicore/titancore/app/TitanCore/` to host
2. Register `TitanCoreServiceProvider` in `config/app.php` (or auto-discover)
3. Copy `config/titan_core.php` to host config
4. Add `routes/core/titan_core.routes.php` to route loader
5. Copy `TitanCoreStatusController` and related controllers
6. Upgrade `AiChatbotModelController` to route through `TitanAIRouter` (non-destructive)
7. Bind `TitanAIRouter` singleton per `docs/titancore/04_TITAN_AI_ROUTER_BINDING.md`

**Conflict mitigations:**
- Do NOT overwrite `app/Http/Kernel.php`
- Do NOT overwrite `routes/web.php`, `api.php`, `panel.php`
- Do NOT copy non-Titan migrations
- Namespace: `App\TitanCore\` (source) migrates to `App\TitanCore\` (host)

**Success criteria:** `TitanCoreServiceProvider` boots, `CoreKernel::status()` returns, TitanCore UI page resolves.

---

## Prompt 3 — TitanMemory and Knowledge Layer

**Goal:** Wire `TitanMemory` (MemoryManager + KnowledgeManager), integrate `laravel-rag` for embeddings.

**Tasks:**
1. Integrate `MemoryManager`, `MemorySnapshot`, `SessionHandoffManager`
2. Integrate `KnowledgeManager`, `KnowledgeScopeResolver`
3. Evaluate `laravel-rag-main/` as vendor package (pgvector / sqlite-vec)
4. Bind `TitanMemory` singleton
5. Attach memory updates to signal lifecycle events
6. Add schema for memory storage (scoped by `company_id`)
7. Verify `MemorySnapshot` integrates with `RewindManager`

**Conflict mitigations:**
- Memory tables use `tz_` prefix per `docs/titancore/24_TZ_SCHEMA_PREFIX_DOCTRINE.md`
- No duplicate Customer/Company schema

---

## Prompt 4 — MCP Bridge and Tool Registry

**Goal:** Expose `titan.<domain>.<action>` MCP tools via `laravel-mcp-sdk`, usable from Claude Desktop and ChatGPT Desktop.

**Tasks:**
1. Integrate `laravel-mcp-sdk-main/` or `mcp-main/` as vendor package
2. Register `ToolRegistry` and wire MCP transport (HTTP/stdio)
3. Implement core tools: `titan.crm.*`, `titan.work.*`, `titan.finance.*`, `titan.signal.dispatch`, `titan.memory.recall`
4. Route all tool calls through `TitanAIRouter`
5. Apply approval lifecycle to tool execution
6. Add Rewind snapshot on tool execution
7. Expose MCP endpoint under `routes/api.php` with Sanctum auth
8. Add tool: `titan.agent.trigger`, `titan.pulse.execute`

**Conflict mitigations:**
- No duplicate tool definitions
- All tools pass through TitanAIRouter per contract
- Route protected by Sanctum; no unauthenticated access

---

## Prompt 5 — Pulse Automation Engine and Agent Studio

**Goal:** Activate `PulseManager` (automation rules engine) and `AgentStudioManager` (agent orchestration).

**Tasks:**
1. Import `PulseManager` — wire to Signal subscribers
2. Import `AgentStudioManager` — wire to TitanAIRouter
3. Integrate `laravel-loop-main/` for agent loop runtime
4. Integrate Zylos bridge: `ZylosBridge` class wrapping `zylos-core-main/` CLI
5. Add Pulse automation rule storage (schema, CRUD)
6. Add Agent Studio agent creation/lifecycle storage
7. Wire PM2 supervision via Zylos for async skill execution

**Conflict mitigations:**
- Zylos operates as a sidecar; no Laravel infrastructure replacement
- PM2 supervision via Zylos is isolated from Laravel queue workers

---

## Prompt 6 — Omni Conversational Layer and Mobile Surface

**Goal:** Activate `OmniManager` (unified chat/voice surface) and confirm all mobile app surfaces work.

**Tasks:**
1. Import `OmniManager` — wire to TitanAIRouter
2. Upgrade embedded chatbot widget (AiChatbotModelController) fully to Omni layer
3. Integrate `ArtCore-main/` channel adapters for Flutter/PWA bridge
4. Verify TitanPortal, TitanCommand, TitanGo, TitanMoney, TitanPro API surface
5. Verify all surfaces use same TitanAIRouter / TitanMemory / Signal contracts
6. Final validation: all 6 UI surfaces operational

**Conflict mitigations:**
- No mobile-specific AI stacks
- Flutter apps use Sanctum API exclusively

---

## Cross-Prompt Constraints

| Rule | Applies To |
|------|-----------|
| `company_id` tenant scope on all Titan DB writes | All prompts |
| `tz_` table prefix for new Titan tables | Prompts 3–6 |
| No duplicate CRM/Work/Finance model creation | All prompts |
| All AI execution through TitanAIRouter | Prompts 2–6 |
| All tool calls log + remain rewind-compatible | Prompts 4–6 |
| Signal pipeline (`app/Titan/Signals/`) is read-only from TitanCore | All prompts |
| Sanctum API is canonical for external/mobile access | All prompts |
