# TITAN AI INVENTORY

Generated: Prompt 1 — AI Component Classification

---

## Purpose

This document catalogs all AI-capable components identified across the host and source archives, classifies them by subsystem, and identifies readiness for integration.

---

## Zero Core

| Component | Source | Readiness | Notes |
|-----------|--------|-----------|-------|
| `CoreKernel` | titancore `app/TitanCore/Zero/CoreKernel.php` | ✅ Source-ready | Entry point for Zero runtime |
| `ZeroCoreManager` | titancore `app/TitanCore/Zero/AI/ZeroCoreManager.php` | ✅ Source-ready | TitanAIRouter precursor |
| `RuntimeManager` | titancore `app/TitanCore/Zero/AI/Runtime/RuntimeManager.php` | ✅ Source-ready | Provider routing |
| `NullRuntimeAdapter` | titancore `app/TitanCore/Zero/AI/Runtime/NullRuntimeAdapter.php` | ✅ Source-ready | Safe default before real router |
| `RuntimeAdapterContract` | titancore | ✅ Source-ready | Defines provider interface |
| `NexusCoordinator` | titancore `app/TitanCore/Zero/AI/Nexus/NexusCoordinator.php` | ✅ Source-ready | Multi-core consensus |
| `ConsensusCoordinator` | titancore | ✅ Source-ready | Vote aggregation |
| `CritiqueLoopEngine` | titancore | ✅ Source-ready | Self-critique AI loop |
| `InstructionBuilder` | titancore | ✅ Source-ready | Context compilation |
| `DecisionContextFactory` | titancore | ✅ Source-ready | Per-request context |
| `AuthorityWeights` | titancore | ✅ Source-ready | Core scoring |
| Nexus Cores (7 lenses) | titancore | ✅ Source-ready | Logi, Creator, Finance, Micro, Macro, Entropy, Equilibrium |
| **TitanAIRouter** | Not yet implemented | 🔴 Prompt 2 | Canonical router — wraps ZeroCoreManager |

---

## TitanMemory

| Component | Source | Readiness | Notes |
|-----------|--------|-----------|-------|
| `MemoryManager` | titancore `app/TitanCore/Zero/Memory/MemoryManager.php` | ✅ Source-ready | Core memory storage |
| `MemorySnapshot` | titancore | ✅ Source-ready | Snapshot capture for Rewind |
| `SessionHandoffManager` | titancore | ✅ Source-ready | Cross-session handoff |
| `KnowledgeManager` | titancore | ✅ Source-ready | Knowledge lookup |
| `KnowledgeScopeResolver` | titancore | ✅ Source-ready | Tenant-scoped resolution |
| **laravel-rag** | AICores `laravel-rag-main/` | ✅ External package | Vector embeddings, RAG pipeline, pgvector |
| **TitanMemory contract** | Not yet implemented | 🔴 Prompt 3 | Unified memory access interface |

---

## Process Lifecycle Engine

| Component | Source | Readiness | Notes |
|-----------|--------|-----------|-------|
| `ProcessStateMachine` | host `app/Titan/Signals/ProcessStateMachine.php` | ✅ Deployed | State transition engine |
| `ProcessRecorder` | host `app/Titan/Signals/ProcessRecorder.php` | ✅ Deployed | Records state transitions to DB |
| `ProcessBridge` | titancore `app/TitanCore/Zero/Process/ProcessBridge.php` | ✅ Source-ready | Links TitanCore to host process engine |
| `SignalBridge` | titancore `app/TitanCore/Zero/Signals/SignalBridge.php` | ✅ Source-ready | Links TitanCore to host Signal pipeline |
| Lifecycle states | docs/titancore/21_LIFECYCLE_ENGINE_STATE_MACHINE.md | ✅ Documented | enquiry→quote→approved→scheduled→service_job→completed→invoiced→paid→retention |

---

## Signal Adapters

| Component | Source | Readiness | Notes |
|-----------|--------|-----------|-------|
| `SignalDispatcher` | host `app/Titan/Signals/SignalDispatcher.php` | ✅ Deployed | |
| `SignalsService` | host `app/Titan/Signals/SignalsService.php` | ✅ Deployed | |
| `EnvelopeBuilder` | host `app/Titan/Signals/EnvelopeBuilder.php` | ✅ Deployed | |
| `SignalNormalizer` | host `app/Titan/Signals/SignalNormalizer.php` | ✅ Deployed | |
| `SignalRegistry` | host `app/Titan/Signals/SignalRegistry.php` | ✅ Deployed | |
| `SignalPriorityEngine` | host `app/Titan/Signals/SignalPriorityEngine.php` | ✅ Deployed | |
| `SignalValidator` | host `app/Titan/Signals/SignalValidator.php` | ✅ Deployed | |

---

## Rewind Hooks

| Component | Source | Readiness | Notes |
|-----------|--------|-----------|-------|
| `RewindSubscriber` | host `app/Titan/Signals/Subscribers/RewindSubscriber.php` | ✅ Deployed | Hooks into signal events |
| `RewindManager` | titancore `app/TitanCore/Zero/Rewind/RewindManager.php` | ✅ Source-ready | TitanCore Rewind integration |
| Rewind routes | host `routes/core/rewind.routes.php` | ✅ Deployed | |

---

## Pulse Automation Engine

| Component | Source | Readiness | Notes |
|-----------|--------|-----------|-------|
| `PulseSubscriber` | host `app/Titan/Signals/Subscribers/PulseSubscriber.php` | ✅ Deployed | Signal-driven trigger listener |
| `PulseManager` | titancore `app/TitanCore/Pulse/PulseManager.php` | ✅ Source-ready | Full Pulse automation runtime |
| MoneySignalsProvider | host `app/Titan/Signals/Providers/MoneySignalsProvider.php` | ✅ Deployed | Finance signal triggers |
| WorkSignalsProvider | host `app/Titan/Signals/Providers/WorkSignalsProvider.php` | ✅ Deployed | Work signal triggers |

---

## Omni Conversational Layer

| Component | Source | Readiness | Notes |
|-----------|--------|-----------|-------|
| `OmniManager` | titancore `app/TitanCore/Omni/OmniManager.php` | ✅ Source-ready | Unified conversation surface manager |
| `AiChatbotModelController` | host — upgrade required | ⚠️ Prompt 2 | Must route through TitanAIRouter |
| `AIChatController` | host — review required | ⚠️ Prompt 3 | Extended version in source; merge |
| ArtCore | AICores `ArtCore-main/` | 🔵 Deferred | Channel adapters |
| Widget bridge | docs/titancore/26_CHATBOT_BUILDER_TO_PWA_BRIDGE.md | 🔵 Prompt 5 | Flutter/PWA channel bridge |

---

## Agent Studio Runtime

| Component | Source | Readiness | Notes |
|-----------|--------|-----------|-------|
| `AgentStudioManager` | titancore `app/TitanCore/Agents/AgentStudioManager.php` | ✅ Source-ready | Agent orchestration entry point |
| `laravel-loop` | AICores `laravel-loop-main/` | ✅ External package | Agent loop runtime |
| `aiox-core` | AICores `aiox-core-main/` | 🔵 Deferred | Go-based agent orchestration |

---

## MCP Bridge / Runtime

| Component | Source | Readiness | Notes |
|-----------|--------|-----------|-------|
| `ToolRegistry` | titancore `app/TitanCore/Registry/Tools/ToolRegistry.php` | ✅ Source-ready | Tool catalog |
| `ToolDefinition` | titancore `app/TitanCore/Registry/Tools/ToolDefinition.php` | ✅ Source-ready | Tool schema |
| `RuntimeCatalog` | titancore | ✅ Source-ready | Runtime catalog |
| `laravel-mcp-sdk` | AICores `laravel-mcp-sdk-main/` | ✅ External package | Full MCP transport (HTTP, WebSocket, stdio) |
| `mcp-main` | AICores `mcp-main/` | ✅ External package | Alternative MCP implementation |
| MCP tool contract | docs/titancore/22_MCP_TOOL_REGISTRY_CONTRACT.md | ✅ Documented | `titan.<domain>.<action>` naming |

---

## Zylos Console Bridge

| Component | Source | Readiness | Notes |
|-----------|--------|-----------|-------|
| `zylos-core-main` | AICores `zylos-core-main/` | ✅ External tool | Node.js CLI + PM2 management |
| Laravel bridge contract | docs/titancore/07_ZYLOS_BRIDGE_SETUP.md | ✅ Documented | Async skill orchestration via Laravel |
| `ZylosBridgeClass` | docs/titancore/08_ZYLOS_BRIDGE_CLASS_TEMPLATE.md | ✅ Template ready | Bridge class template |

---

## Telemetry / Governance

| Component | Source | Readiness | Notes |
|-----------|--------|-----------|-------|
| `TelemetryManager` | titancore `app/TitanCore/Zero/Telemetry/TelemetryManager.php` | ✅ Source-ready | Runtime telemetry |
| `AuditTrail` | host `app/Titan/Signals/AuditTrail.php` | ✅ Deployed | Signal audit log |
| `ApprovalChain` | host `app/Titan/Signals/ApprovalChain.php` | ✅ Deployed | Approval governance |
| Approval states | docs/titancore/29_AI_APPROVAL_GOVERNANCE_MODEL.md | ✅ Documented | suggestion→processing→approval_required→approved→executed→rewound |
