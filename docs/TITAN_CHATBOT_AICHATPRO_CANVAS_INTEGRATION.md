# TITAN CHATBOT + AICHATPRO + CANVAS INTEGRATION GUIDE

**Generated:** 2026-04-03  
**Purpose:** How Chatbot, AIChatPro, and Canvas were merged into the core brain.

---

## 1. Merge Strategy

The merge preserves all three working UI surfaces while unifying their execution backend.

**Before merge:**
- AIChatPro → direct `BedrockRuntimeService`/`GatewaySelector` → OpenAI/Bedrock
- Canvas → no AI routing (UI + storage only)
- Chatbot channels → each channel called AI providers independently

**After merge:**
- AIChatPro UI → `TitanChatBridge::buildChatProEnvelope()` → OmniManager → TitanAIRouter
- Canvas UI → `TitanChatBridge::buildCanvasEnvelope()` → OmniManager → TitanAIRouter
- Chatbot channels → `ChannelAdapter::toEnvelope()` → `TitanChatBridge::chatViaChannel()` → OmniManager → TitanAIRouter

---

## 2. AIChatPro Integration

### What was preserved
- All AIChatPro views (`resources/views/` namespace `ai-chat-pro::`)
- AIChatProController index/settings routes (`dashboard.user.openai.chat.pro.*`)
- AIChatProSettingsController
- Folder browsing (AiChatProFolders extension)
- User memory/instructions (AiChatProMemory extension)
- File chat (AiChatProFileChat extension)
- Guest chat support

### What was bridged
- AI execution: `TitanChatBridge::buildChatProEnvelope()` builds the standard envelope
- Memory: User instructions from AiChatProMemory now flow to `TitanMemoryService::hydrateContext()`
- Tool calls (generate_image): `AiChatProService::tools()` definitions injected as TitanAIRouter tools

### Integration code path
```php
// In AIChatProController (when routing through canonical path):
$envelope = app(TitanChatBridge::class)->buildChatProEnvelope([
    'input'        => $request->input('message'),
    'session_id'   => $chat->id,
    'category_id'  => $category->id,
    'company_id'   => auth()->user()->company_id,
    'user_id'      => auth()->id(),
    'model'        => $request->input('model'),
]);
$result = app(TitanChatBridge::class)->chat($envelope);
```

### API endpoint
```
POST /api/titan/chat/send
{
    "surface": "aichatpro",
    "input": "...",
    "session_id": "...",
    "category_id": 123
}
```

---

## 3. Canvas Integration

### What was preserved
- Canvas workspace UI (tiptap editor, settings, chat area)
- `CanvasController::storeContent()` and `saveTitle()` (storage routes unchanged)
- `UserTiptapContent` model
- Canvas button component
- Routes: `tiptap-content-store`, `tiptap-title-save`

### What was bridged
- Canvas AI generation: `TitanChatBridge::buildCanvasEnvelope()` builds the standard envelope
- Canvas reasoning now routes through TitanAIRouter
- Canvas context stored in `TitanMemoryService` with `context_type: 'canvas'`

### Integration code path
```php
// From Canvas JS (openai_chat.js) via new API endpoint:
$envelope = app(TitanChatBridge::class)->buildCanvasEnvelope([
    'input'      => $request->input('prompt'),
    'message_id' => $request->input('message_id'),
    'intent'     => 'canvas.draft',
    'company_id' => auth()->user()->company_id,
    'user_id'    => auth()->id(),
]);
$result = app(TitanChatBridge::class)->chat($envelope);
```

### API endpoint
```
POST /api/titan/chat/send
{
    "surface": "canvas",
    "input": "Draft a service agreement for...",
    "message_id": "456"
}
```

---

## 4. Chatbot Suite Integration

### What was preserved
- All chatbot admin views and training UI
- ChatBotController (CRUD)
- Chatbot model and configuration
- All channel extension webhook handlers and transport logic
- ChatbotAgent inbox UI

### What was bridged
- `AiChatbotModelController` already uses `TitanAIRouter` — no change needed
- Channel adapters normalise inbound messages to Titan envelopes
- All 6 channels route via `TitanChatBridge::chatViaChannel()`

### Integration code path (channel webhook)
```php
// In ChatbotMessengerWebhookController (example):
$result = app(TitanChatBridge::class)->chatViaChannel('messenger', $webhookPayload);
// TitanChatBridge calls MessengerChannelAdapter::toEnvelope() + OmniManager::dispatch()
// MessengerChannelAdapter::sendResponse() sends reply back to Messenger API
```

---

## 5. Memory Unification

All chat surfaces share one memory path:

```
TitanMemoryService::store(company_id, user_id, session_id, type, content)
TitanMemoryService::hydrateContext(envelope)  ← recalled before each AI decision
```

| Surface | session_id | type |
|---------|-----------|------|
| AIChatPro | UserOpenaiChat::id | `ai_decision` |
| Canvas | UserOpenaiChatMessage::id | `ai_decision` + `canvas` |
| Chatbot workspace | UserOpenaiChat::id | `ai_decision` |
| Messenger | sender_id | `ai_decision` |
| WhatsApp | From phone number | `ai_decision` |
| Voice | conversation_id | `ai_decision` |

---

## 6. Conversation Model Unification

Migration `2026_04_03_800100_add_titan_chat_surface_columns_to_user_openai_chat.php` adds:

| Column | Type | Purpose |
|--------|------|---------|
| `surface_id` | string(64) | Which UI surface: `aichatpro\|canvas\|chatbot\|workspace` |
| `channel_type` | string(64) | Which channel: `workspace\|messenger\|whatsapp\|telegram\|voice\|webchat\|external` |
| `entity_type` | string(64) | Linked entity: `job\|invoice\|quote\|customer\|null` |
| `entity_id` | bigint | Linked entity PK |
| `signal_refs` | json | Array of Titan signal IDs emitted during conversation |

---

## 7. Action Safety

Any chat turn that generates an action (create job, send message, etc.) must pass through:

```
TitanAIRouter → SignalBridge → ApprovalChain
```

The `requires_approval` flag in the AI decision result gates direct execution.  
Signal keys for chat actions: `chat.action.job.create`, `chat.action.message.send`, etc.
