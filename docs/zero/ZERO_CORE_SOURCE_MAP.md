# ZERO CORE SOURCE MAP

Generated: Prompt 1 — Source Extraction and Classification Pass

---

## Source Archives

| Archive | Extracted To | Role |
|---------|-------------|------|
| `CodeToUse/zero_core.zip` | `CodeToUse/aicore/titancore/` | TitanCore host foundation (Laravel app snapshot) |
| `CodeToUse/AICores.zip` | `CodeToUse/aicore/AICores/` | AI runtime sub-projects bundle |

---

## titancore Contents (`CodeToUse/aicore/titancore/`)

### Zero Core Layer — `app/TitanCore/`

| Path | Classification | Merge Target |
|------|---------------|-------------|
| `app/TitanCore/Contracts/CoreModuleContract.php` | **Zero Core / contracts** | `app/Titan/Core/Contracts/` |
| `app/TitanCore/Registry/CoreModuleRegistry.php` | **Zero Core / registry** | `app/Titan/Core/Registry/` |
| `app/TitanCore/Registry/CoreModuleDefinition.php` | **Zero Core / registry** | `app/Titan/Core/Registry/` |
| `app/TitanCore/Registry/CoreManifest.php` | **Zero Core / registry** | `app/Titan/Core/Registry/` |
| `app/TitanCore/Registry/Runtime/RuntimeCatalog.php` | **Zero Core / registry** | `app/Titan/Core/Registry/` |
| `app/TitanCore/Registry/Runtime/RuntimeDefinition.php` | **Zero Core / registry** | `app/Titan/Core/Registry/` |
| `app/TitanCore/Registry/Tools/ToolRegistry.php` | **MCP Runtime / tool registry** | `app/Titan/Core/Registry/` |
| `app/TitanCore/Registry/Tools/ToolDefinition.php` | **MCP Runtime / tool registry** | `app/Titan/Core/Registry/` |
| `app/TitanCore/Support/CoreSourceCatalog.php` | **Zero Core / context** | `app/Titan/Core/` |
| `app/TitanCore/Zero/CoreKernel.php` | **Zero Core / router** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Zero/AI/ZeroCoreManager.php` | **Zero Core / router** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Zero/AI/Runtime/RuntimeAdapterContract.php` | **Zero Core / contracts** | `app/Titan/Core/Contracts/` |
| `app/TitanCore/Zero/AI/Runtime/NullRuntimeAdapter.php` | **Zero Core / router** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Zero/AI/Runtime/RuntimeManager.php` | **Zero Core / router** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Zero/AI/Context/InstructionBuilder.php` | **Zero Core / context** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Zero/AI/Context/DecisionContextFactory.php` | **Zero Core / context** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Zero/AI/Consensus/ConsensusCoordinator.php` | **Zero Core / governance** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Zero/AI/Nexus/NexusCoreContract.php` | **Zero Core / contracts** | `app/Titan/Core/Contracts/` |
| `app/TitanCore/Zero/AI/Nexus/AbstractNexusCore.php` | **Zero Core / router** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Zero/AI/Nexus/AuthorityWeights.php` | **Zero Core / governance** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Zero/AI/Nexus/CritiqueLoopEngine.php` | **Zero Core / governance** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Zero/AI/Nexus/RoundRobinRefinement.php` | **Zero Core / router** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Zero/AI/Nexus/UnifiedContextPackBuilder.php` | **Zero Core / context** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Zero/AI/Nexus/NexusCoordinator.php` | **Zero Core / router** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Zero/AI/Nexus/Cores/*.php` (7 cores) | **Zero Core / router** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Zero/Signals/SignalBridge.php` | **Zero Core / signal adapters** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Zero/Process/ProcessBridge.php` | **Zero Core / signal adapters** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Zero/Knowledge/KnowledgeManager.php` | **Zero Core / memory** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Zero/Knowledge/KnowledgeScopeResolver.php` | **Zero Core / memory** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Zero/Memory/MemoryManager.php` | **Zero Core / memory** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Zero/Memory/MemorySnapshot.php` | **Zero Core / memory** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Zero/Memory/Session/SessionHandoffManager.php` | **Zero Core / memory** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Zero/Rewind/RewindManager.php` | **Rewind hooks** | Bridge to existing host Rewind scaffolding |
| `app/TitanCore/Zero/Telemetry/TelemetryManager.php` | **Zero Core / governance** | `app/Titan/Core/Zero/` |
| `app/TitanCore/Pulse/PulseManager.php` | **Pulse / automation** | `app/Titan/Core/Pulse/` |
| `app/TitanCore/Omni/OmniManager.php` | **Omni / chat** | `app/Titan/Core/Omni/` |
| `app/TitanCore/Agents/AgentStudioManager.php` | **Agent Studio / lifecycle** | `app/Titan/Core/Agents/` |

### Signal Layer — `app/Titan/Signals/` (already in host)

| Path | Classification | Status |
|------|---------------|--------|
| `app/Titan/Signals/Signal.php` | **Zero Core / signal adapters** | ✅ Deployed in host |
| `app/Titan/Signals/SignalDispatcher.php` | **Zero Core / signal adapters** | ✅ Deployed in host |
| `app/Titan/Signals/SignalsService.php` | **Zero Core / signal adapters** | ✅ Deployed in host |
| `app/Titan/Signals/EnvelopeBuilder.php` | **Zero Core / signal adapters** | ✅ Deployed in host |
| `app/Titan/Signals/ProcessStateMachine.php` | **Process lifecycle** | ✅ Deployed in host |
| `app/Titan/Signals/ProcessRecorder.php` | **Process lifecycle** | ✅ Deployed in host |
| `app/Titan/Signals/ApprovalChain.php` | **Zero Core / governance** | ✅ Deployed in host |
| `app/Titan/Signals/AuditTrail.php` | **Zero Core / governance** | ✅ Deployed in host |
| `app/Titan/Signals/Subscribers/PulseSubscriber.php` | **Pulse / subscriptions** | ✅ Deployed in host |
| `app/Titan/Signals/Subscribers/ZeroSubscriber.php` | **Zero Core / signal adapters** | ✅ Deployed in host |
| `app/Titan/Signals/Subscribers/RewindSubscriber.php` | **Rewind hooks** | ✅ Deployed in host |
| `app/Titan/Signals/Providers/MoneySignalsProvider.php` | **Pulse / triggers** | ✅ Deployed in host |
| `app/Titan/Signals/Providers/WorkSignalsProvider.php` | **Pulse / triggers** | ✅ Deployed in host |

### Infrastructure — Source Only (Duplicate / Mark for Exclusion)

| Path | Reason |
|------|--------|
| `app/Http/Controllers/AIController.php` | Duplicate — host owns AIChatController |
| `app/Http/Controllers/AiChatbotModelController.php` | Override — host owns this; upgrade in Prompt 2 |
| `app/Http/Controllers/AIChatController.php` | Duplicate — host owns this |
| `app/Http/Kernel.php` | Duplicate infrastructure |
| `database/migrations/*` (non-Titan) | Duplicate — host already owns these schemas |
| `composer.json`, `package.json`, `bootstrap/` | Duplicate host infrastructure |
| `routes/web.php`, `routes/api.php`, `routes/auth.php`, `routes/panel.php` | Duplicate — host owns route loading |
| `config/` (non-Titan) | Duplicate host config |

### New Assets from Source (not yet in host)

| Path | Classification | Priority |
|------|---------------|---------|
| `app/TitanCore/` (full tree) | Zero Core | **Prompt 2** |
| `app/Providers/TitanCoreServiceProvider.php` | Zero Core provider | **Prompt 2** |
| `routes/core/titan_core.routes.php` | Titan UI route | **Prompt 2** |
| `config/titan_core.php` | Titan config | **Prompt 2** |
| `app/Http/Controllers/TitanCore/` | Titan UI controllers | **Prompt 2** |

---

## AICores Contents (`CodeToUse/aicore/AICores/`)

| Sub-project | Language | Classification | Merge Priority |
|-------------|----------|---------------|----------------|
| `zylos-core-main/` | Node.js CLI | **Zylos / console, monitoring** | Prompt 5 |
| `laravel-mcp-sdk-main/` | PHP/Laravel | **MCP Runtime / transport, tool registry** | Prompt 4 |
| `mcp-main/` | PHP/Laravel | **MCP Runtime / endpoint definitions** | Prompt 4 |
| `laravel-rag-main/` | PHP/Laravel | **Zero Core / memory + embeddings** | Prompt 3 |
| `laravel-loop-main/` | PHP/Laravel | **Agent Studio / agent lifecycle** | Prompt 4 |
| `ArtCore-main/` | Node.js | **Omni / channel adapters** | Prompt 5 |
| `CommerceCore-main/` | PHP/Laravel | Bridge to host Finance domain | Prompt 6 |
| `aiox-core-main/` | Go/CLI | **Agent Studio / orchestration** | Prompt 5 |
| `EdgeChains-ts/` | TypeScript/Rust | **MCP Runtime / transport** | Prompt 6 |
