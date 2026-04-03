# TITAN CHAT CONNECTION MAP

**Generated:** 2026-04-03  
**Purpose:** Every host-codebase integration point for the merged chat system.

---

## 1. Execution Pipeline (canonical path)

```
Chat UI / Channel Webhook
        │
        ▼
TitanChatBridge::chat() / chatViaChannel()
        │
        ▼
OmniManager::dispatch()          ← app/TitanCore/Omni/OmniManager.php
        │  normalises envelope
        ▼
TitanAIRouter::execute()         ← app/TitanCore/Zero/AI/TitanAIRouter.php
        │  budget check
        │  memory recall (TitanMemoryService::hydrateContext)
        ▼
ZeroCoreManager::decide()        ← app/TitanCore/Zero/AI/ZeroCoreManager.php
        │  nexus + consensus
        ▼
SignalBridge::recordAndPublish() ← app/TitanCore/Zero/Signals/SignalBridge.php
        │  approval / rewind events
        ▼
TitanMemoryService::store()      ← app/Titan/Core/TitanMemoryService.php
        │  persist decision
        ▼
Result returned to caller
```

---

## 2. Controller Connection Points

| Controller | Location | Connects To |
|-----------|----------|-------------|
| AiChatbotModelController | `app/Http/Controllers/AiChatbotModelController.php` | TitanAIRouter (already wired) |
| AIChatController | `app/Http/Controllers/AIChatController.php` | Direct streaming; TitanChatBridge for non-stream turns |
| ChatBotController | `app/Http/Controllers/ChatBotController.php` | Chatbot CRUD; delegates to AIChatController for execution |
| TitanChatBridgeController | `app/Http/Controllers/TitanCore/TitanChatBridgeController.php` | TitanChatBridge (new) |
| AIChatProController (ext) | `app/.../AIChatPro/.../AIChatProController.php` | TitanChatBridge via `buildChatProEnvelope()` |
| CanvasController (ext) | `app/.../Canvas/.../CanvasController.php` | UserTiptapContent (storage); TitanChatBridge for generation |

---

## 3. Memory Connection Points

| Consumer | Connects To | Notes |
|---------|------------|-------|
| TitanAIRouter (canonical) | TitanMemoryService | Memory recall before decide(); store after |
| AiChatProMemory extension | UserChatInstruction → TitanMemoryService | User instruction overrides |
| Canvas context memory | UserTiptapContent + TitanMemoryService session | Canvas document state |
| ChatbotVoice conversations | ExtVoicechabotConversation → TitanMemoryService | Voice history |
| All channel adapters | via OmniManager::dispatch() → TitanAIRouter | Unified memory path |

**Canonical memory table:** `tz_ai_memories` (App\Titan\Core\TitanMemoryService)

---

## 4. Message/Thread Storage Connection Points

| Table | Model | Owner |
|-------|-------|-------|
| `user_openai_chat` | UserOpenaiChat | AIChatPro / Chatbot conversations |
| `user_openai_chat_messages` | UserOpenaiChatMessage | Per-message storage (+ canvas tiptap_content) |
| `user_tiptap_content` | UserTiptapContent (Canvas ext) | Canvas document state |
| `tz_ai_memories` | (raw DB, TitanMemoryService) | Titan memory entries |
| `tz_ai_memory_snapshots` | (raw DB) | Session snapshots |
| `ext_voice_chatbot_conversations` | ExtVoicechabotConversation | Voice chatbot history |
| `ext_voice_chatbot_histories` | ExtVoicechatbotHistory | Per-turn voice history |

---

## 5. Signal / Approval / Action Connection Points

All chat actions that create or mutate business entities MUST pass through:

```
TitanAIRouter → SignalBridge → Approval chain
```

| Action | Signal Key | Notes |
|--------|-----------|-------|
| Create job from chat | `chat.action.job.create` | Queued for approval |
| Send external message | `chat.action.message.send` | Signal recorded |
| Generate quote | `chat.action.quote.generate` | Approval required |
| Update entity | `chat.action.entity.update` | Signal + audit trail |

Signals table: `titan_signals` (App\Titan\Signals\SignalsService)

---

## 6. Route Connection Points

### Existing routes (preserved)
| Route name | Method | Path | Controller |
|-----------|--------|------|-----------|
| `dashboard.user.openai.chat.pro.index` | GET | `/dashboard/user/openai/chat/pro/chat/{slug?}` | AIChatProController |
| `dashboard.admin.openai.chat.pro.settings` | GET/POST | `/dashboard/admin/openai/chat/pro/settings` | AIChatProSettingsController |
| `tiptap-content-store` | POST | `/tiptap-content-store` | CanvasController |
| `tiptap-title-save` | POST | `/tiptap-title-save` | CanvasController |
| Various `/aichat/*` | GET/POST | `/aichat/*` | AIChatController (API) |

### New routes (added by this pass)
| Route name | Method | Path | Controller |
|-----------|--------|------|-----------|
| `titan.chat.send` | POST | `/api/titan/chat/send` | TitanChatBridgeController |
| `titan.chat.status` | GET | `/api/titan/chat/status` | TitanChatBridgeController |

---

## 7. Model Connection Points

| Model | Used By | Notes |
|-------|---------|-------|
| UserOpenaiChat | AIChatPro, Chatbot, AIChatController | Core conversation model; extended with surface_id, channel_type, entity_type, entity_id, signal_refs |
| UserOpenaiChatMessage | AIChatPro, Canvas, AIChatController | Per-message model |
| OpenaiGeneratorChatCategory | AIChatPro, Chatbot | Chat category/persona config |
| Chatbot | ChatBotController, AIChatController | Chatbot config |
| UserTiptapContent | Canvas | Canvas document state |
| UserChatInstruction | AiChatProMemory | User system-prompt overrides |

---

## 8. Auth / Tenancy Connection Points

All chat surfaces must respect:

- `company_id` — Titan tenant boundary (from Auth user)  
- `user_id` — authenticated user (nullable for guest chats)  
- `team_id` — team scope (only used for team-shared conversations, mapped to company_id)  

Middleware used:
- `auth` / `auth:sanctum`  
- `EnforceTitanTenancy` (API routes)  
- `updateUserActivity`  

---

## 9. PWA / Mobile Connection Points

| Surface | Connection |
|---------|-----------|
| PWA offline chat | PwaDeferredReplayService queues chat turns; replays via `titan.chat.send` on reconnect |
| TitanCommand mobile | Uses REST API: `POST /api/titan/chat/send` |
| TitanGo mobile | Same REST API |
| Service Worker | `titan-zero-v3` SW caches static chat assets |

---

## 10. Admin / Monitoring Connection Points

| Surface | Path | Notes |
|---------|------|-------|
| Chatbot admin | `/dashboard/admin/chatbot/*` | Chatbot CRUD, training data, AI models |
| AI Models admin | `AiChatbotModelController::index()` | Model selection; routes through TitanAIRouter |
| TitanCore status | `/dashboard/user/business-suite/core/` | TitanCoreStatusController |
| Telemetry | TelemetryManager | All chat dispatches recorded |
