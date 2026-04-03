# TITAN CHAT CANONICAL ARCHITECTURE

**Generated:** 2026-04-03  
**Purpose:** Defines which files/classes are canonical, which are legacy, and which are bridged.

---

## 1. Canonical Architecture Diagram

```
┌──────────────────────────────────────────────────────────────────────┐
│                        CHAT SURFACES (UI)                            │
│                                                                      │
│  AIChatPro UI     Canvas UI     Chatbot Widget     Channel Adapters  │
│  (preserved)      (preserved)   (preserved)        (6 adapters)      │
└────────────────────────────────┬─────────────────────────────────────┘
                                 │
                                 ▼
┌──────────────────────────────────────────────────────────────────────┐
│                      TitanChatBridge                                 │
│   app/Services/TitanChat/TitanChatBridge.php                        │
│                                                                      │
│   - buildChatProEnvelope()    ← AIChatPro surface                    │
│   - buildCanvasEnvelope()     ← Canvas surface                       │
│   - chat()                    ← Generic workspace                    │
│   - chatViaChannel()          ← Channel adapters                     │
└────────────────────────────────┬─────────────────────────────────────┘
                                 │
                                 ▼
┌──────────────────────────────────────────────────────────────────────┐
│                        OmniManager                                   │
│   app/TitanCore/Omni/OmniManager.php                                 │
│                                                                      │
│   - ingest()    ← telemetry only                                     │
│   - dispatch()  ← normalise + route to TitanAIRouter  (NEW)         │
└────────────────────────────────┬─────────────────────────────────────┘
                                 │
                                 ▼
┌──────────────────────────────────────────────────────────────────────┐
│                       TitanAIRouter                                  │
│   app/TitanCore/Zero/AI/TitanAIRouter.php                            │
│                                                                      │
│   - Budget enforcement                                               │
│   - Memory recall (TitanMemoryService::hydrateContext)               │
│   - ZeroCoreManager::decide()                                        │
│   - Memory store (TitanMemoryService::store)                         │
│   - SignalBridge::recordAndPublish()                                 │
│   - TitanCoreActivity event                                          │
└────────────────────────────────┬─────────────────────────────────────┘
                                 │
                    ┌────────────┴────────────┐
                    ▼                         ▼
        ┌─────────────────┐       ┌─────────────────────┐
        │ TitanMemoryService│     │    SignalBridge       │
        │ (canonical)      │     │  → Approval chain     │
        │ tz_ai_memories   │     │  → Rewind             │
        └─────────────────┘       └─────────────────────┘
```

---

## 2. Canonical Files

### 2.1 AI Execution

| Class | Path | Status |
|-------|------|--------|
| `TitanAIRouter` | `app/TitanCore/Zero/AI/TitanAIRouter.php` | ✅ CANONICAL — single AI execution entry point |
| `ZeroCoreManager` | `app/TitanCore/Zero/AI/ZeroCoreManager.php` | ✅ CANONICAL — AI decision engine |
| `OmniManager` | `app/TitanCore/Omni/OmniManager.php` | ✅ CANONICAL — extended with `dispatch()` |
| `TitanChatBridge` | `app/Services/TitanChat/TitanChatBridge.php` | ✅ CANONICAL (NEW) — chat surface bridge |
| `NexusCoordinator` | `app/TitanCore/Zero/AI/Nexus/NexusCoordinator.php` | ✅ CANONICAL |
| `ConsensusCoordinator` | `app/TitanCore/Zero/AI/Consensus/ConsensusCoordinator.php` | ✅ CANONICAL |

### 2.2 Memory

| Class | Path | Status |
|-------|------|--------|
| `TitanMemoryService` | `app/Titan/Core/TitanMemoryService.php` | ✅ CANONICAL — DB-backed, rewind-compatible |
| `TitanMemoryService` (old) | `app/TitanCore/Zero/Memory/TitanMemoryService.php` | ⚠️ TOMBSTONE — deprecated, do not use |
| `MemoryManager` | `app/TitanCore/Zero/Memory/MemoryManager.php` | ✅ CANONICAL (used by TitanMemoryService) |

### 2.3 Signals / Approval

| Class | Path | Status |
|-------|------|--------|
| `SignalBridge` | `app/TitanCore/Zero/Signals/SignalBridge.php` | ✅ CANONICAL |
| `SignalsService` | `app/Titan/Signals/SignalsService.php` | ✅ CANONICAL |
| `ApprovalChain` | `app/Titan/Signals/ApprovalChain.php` | ✅ CANONICAL |

### 2.4 Channel Adapters (NEW)

| Class | Path | Status |
|-------|------|--------|
| `ChannelAdapterContract` | `app/TitanCore/Chat/Contracts/ChannelAdapterContract.php` | ✅ CANONICAL (NEW) |
| `MessengerChannelAdapter` | `app/TitanCore/Chat/Channels/MessengerChannelAdapter.php` | ✅ CANONICAL (NEW) |
| `WhatsAppChannelAdapter` | `app/TitanCore/Chat/Channels/WhatsAppChannelAdapter.php` | ✅ CANONICAL (NEW) |
| `TelegramChannelAdapter` | `app/TitanCore/Chat/Channels/TelegramChannelAdapter.php` | ✅ CANONICAL (NEW) |
| `VoiceChannelAdapter` | `app/TitanCore/Chat/Channels/VoiceChannelAdapter.php` | ✅ CANONICAL (NEW) |
| `WebchatChannelAdapter` | `app/TitanCore/Chat/Channels/WebchatChannelAdapter.php` | ✅ CANONICAL (NEW) |
| `ExternalChatbotChannelAdapter` | `app/TitanCore/Chat/Channels/ExternalChatbotChannelAdapter.php` | ✅ CANONICAL (NEW) |

### 2.5 Controllers

| Class | Path | Status |
|-------|------|--------|
| `AiChatbotModelController` | `app/Http/Controllers/AiChatbotModelController.php` | ✅ CANONICAL — already uses TitanAIRouter |
| `AIChatController` | `app/Http/Controllers/AIChatController.php` | ✅ CANONICAL — host chat controller |
| `ChatBotController` | `app/Http/Controllers/ChatBotController.php` | ✅ CANONICAL — chatbot CRUD |
| `TitanChatBridgeController` | `app/Http/Controllers/TitanCore/TitanChatBridgeController.php` | ✅ CANONICAL (NEW) |
| `AIChatProController` (ext) | `app/Extensions/AIChatPro/.../AIChatProController.php` | ✅ SURFACE (PRESERVED) — UI routes untouched |
| `CanvasController` (ext) | `app/Extensions/Canvas/.../CanvasController.php` | ✅ SURFACE (PRESERVED) — storage routes untouched |

---

## 3. Legacy / Deprecated Items

| Item | Reason | Action |
|------|--------|--------|
| `App\TitanCore\Zero\Memory\TitanMemoryService` | Tombstone. Canonical is `App\Titan\Core\TitanMemoryService` | Do not bind, do not inject |
| Direct `OpenAI::chat()` calls in AIChatProController | Bypasses TitanAIRouter budget/memory/signal | Route via TitanChatBridge for new features |
| Direct `BedrockRuntimeService` calls for AI decisions | Bypasses canonical path | Use TitanChatBridge |
| Isolated canvas reasoning engine concept | Canvas is now UI-only | TitanAIRouter handles all reasoning |

---

## 4. Configuration

| Config key | File | Description |
|-----------|------|-------------|
| `titan_core.ai.default_runtime` | `config/titan_core.php` | AI runtime selection |
| `titan_ai.*` | `config/titan_ai.php` | AI router settings |
| `titan_budgets.*` | `config/titan_budgets.php` | Token budget limits |
| `titan_memory.*` | `config/titan_memory.php` | Memory config (optional) |

---

## 5. Service Container Bindings

All registered in `app/Providers/TitanCoreServiceProvider.php`:

```php
// Canonical singletons (existing)
TitanMemoryService::class     → singleton
TitanAIRouter::class          → singleton
OmniManager::class            → singleton

// New (added by this pass)
TitanChatBridge::class        → singleton (auto-registers all 6 channel adapters)
```
