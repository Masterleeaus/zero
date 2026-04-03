# TITAN CHAT EXTENSION INVENTORY

**Generated:** 2026-04-03  
**Source scanned:** `CodeToUse/Extensions/ExtensionLibrary/`, `app/`, `routes/`, `resources/views/`

---

## 1. Overview

This inventory classifies every chat-related extension, controller, service, view, and route found in the repository.  
Each item is tagged with its integration role under the merged Titan Chat Brain.

---

## 2. Extension Classification

### 2.1 AIChatPro Suite

| Item | Path | Classification |
|------|------|----------------|
| AIChatProController | `CodeToUse/Extensions/ExtensionLibrary/AIChatPro/AIChatPro/System/Http/Controllers/AIChatProController.php` | **Canonical surface** — UI preserved, execution now routes via TitanChatBridge |
| AIChatProSettingsController | `CodeToUse/Extensions/ExtensionLibrary/AIChatPro/AIChatPro/System/Http/Controllers/AIChatProSettingsController.php` | **Canonical surface** — admin settings UI |
| AiChatProService | `CodeToUse/Extensions/ExtensionLibrary/AIChatPro/AIChatPro/System/Services/AiChatProService.php` | **Feature donor** — tool definitions (generate_image) wired into TitanAIRouter toolset |
| AIChatProServiceProvider | `CodeToUse/Extensions/ExtensionLibrary/AIChatPro/AIChatPro/System/AIChatProServiceProvider.php` | **Already host-canonical** — registers routes under `dashboard.user.openai.chat.pro.*` |
| index.blade.php | `CodeToUse/Extensions/ExtensionLibrary/AIChatPro/AIChatPro/resources/views/index.blade.php` | **Canonical surface** — main chat workspace UI preserved |
| chat_area_container.blade.php | `CodeToUse/Extensions/ExtensionLibrary/AIChatPro/AIChatPro/resources/views/includes/chat_area_container.blade.php` | **Canonical surface** — chat area UI preserved |

### 2.2 AiChatProMemory Sub-extension

| Item | Path | Classification |
|------|------|----------------|
| AIChatProMemoryController | `CodeToUse/Extensions/ExtensionLibrary/AiChatProMemory/.../AIChatProMemoryController.php` | **Feature donor** — user instruction save/recall bridged to TitanMemory |
| UserChatInstruction model | `CodeToUse/Extensions/ExtensionLibrary/AiChatProMemory/.../UserChatInstruction.php` | **Feature donor** — per-user system prompt overrides |

### 2.3 AiChatProFolders Sub-extension

| Item | Path | Classification |
|------|------|----------------|
| AIChatProFoldersController | `CodeToUse/Extensions/ExtensionLibrary/AiChatProFolders/.../AIChatProFoldersController.php` | **Canonical surface** — folder/context browsing UI preserved |
| AiChatProFolder model | `CodeToUse/Extensions/ExtensionLibrary/AiChatProFolders/.../AiChatProFolder.php` | **Canonical surface** — conversation folder organisation |

### 2.4 AiChatProFileChat Sub-extension

| Item | Path | Classification |
|------|------|----------------|
| File chat controller(s) | `CodeToUse/Extensions/ExtensionLibrary/AiChatProFileChat/` | **Feature donor** — file-context chat wired to TitanAIRouter |

---

### 2.5 Canvas Extension

| Item | Path | Classification |
|------|------|----------------|
| CanvasController | `CodeToUse/Extensions/ExtensionLibrary/Canvas/Canvas/System/Http/Controllers/CanvasController.php` | **Canonical surface** — stores tiptap content; generation routed via TitanChatBridge |
| CanvasServiceProvider | `CodeToUse/Extensions/ExtensionLibrary/Canvas/Canvas/System/CanvasServiceProvider.php` | **Already host-canonical** — registers routes `tiptap-content-store`, `tiptap-title-save` |
| UserTiptapContent model | `CodeToUse/Extensions/ExtensionLibrary/Canvas/Canvas/System/Http/Models/UserTiptapContent.php` | **Canonical surface** — canvas document storage |
| canvas UI views | `CodeToUse/Extensions/ExtensionLibrary/Canvas/Canvas/resources/views/` | **Canonical surface** — workspace, chat area, settings |
| openai_chat.js | `CodeToUse/Extensions/ExtensionLibrary/Canvas/Canvas/resources/assets/js/openai_chat.js` | **Feature donor** — JS chat integration; needs `titan.chat.send` endpoint |
| Migration | `CodeToUse/Extensions/ExtensionLibrary/Canvas/Canvas/database/2025_07_03_140345_create_user_tiptap_contents_table.php` | **Already host-canonical** |

---

### 2.6 Chatbot Suite

| Item | Path | Classification |
|------|------|----------------|
| ChatBotController | `app/Http/Controllers/ChatBotController.php` | **Host-canonical** — existing controller, preserved |
| AiChatbotModelController | `app/Http/Controllers/AiChatbotModelController.php` | **Host-canonical** — already uses TitanAIRouter |
| AIChatController | `app/Http/Controllers/AIChatController.php` | **Host-canonical** — primary chat execution controller |
| ChatbotAgent extension | `CodeToUse/Extensions/ExtensionLibrary/ChatbotAgent/` | **Canonical surface** — agent inbox UI preserved |
| External-Chatbot extension | `CodeToUse/Extensions/ExtensionLibrary/External-Chatbot/` | **Channel adapter** — bridged via ExternalChatbotChannelAdapter |
| ChatbotController (ext) | `CodeToUse/Extensions/ExtensionLibrary/External-Chatbot/app/Extensions/Chatbot/System/Http/Controllers/ChatbotController.php` | **Canonical surface** — chatbot management UI preserved |
| Chatbot views | `resources/views/default/panel/chatbot/` | **Host-canonical** — preserved |
| Chatbot admin views | `resources/views/default/panel/admin/chatbot/` | **Host-canonical** — preserved |

---

### 2.7 Channel Adapter Extensions

| Extension | Channel | Classification |
|-----------|---------|----------------|
| ChatbotMessenger | `messenger` | **Channel adapter** — bridged via MessengerChannelAdapter |
| ChatbotWhatsapp | `whatsapp` | **Channel adapter** — bridged via WhatsAppChannelAdapter |
| ChatbotVoice | `voice` | **Channel adapter** — bridged via VoiceChannelAdapter |
| ChatbotTelegram | `telegram` | **Channel adapter** — bridged via TelegramChannelAdapter |
| Webchat | `webchat` | **Channel adapter** — bridged via WebchatChannelAdapter |
| External-Chatbot | `external` | **Channel adapter** — bridged via ExternalChatbotChannelAdapter |
| ElevenlabsVoiceChat | `voice/tts` | **Feature donor** — TTS synthesis for voice channel |
| OpenaiRealtimeChat | `realtime` | **Feature donor** — realtime voice/text channel |

---

## 3. Host-Canonical AI Infrastructure

These already exist and are **not** duplicated by the merge.

| Component | Path | Role |
|-----------|------|------|
| TitanAIRouter | `app/TitanCore/Zero/AI/TitanAIRouter.php` | Canonical AI execution entry point |
| TitanMemoryService | `app/Titan/Core/TitanMemoryService.php` | Canonical memory: store, recall, snapshot |
| OmniManager | `app/TitanCore/Omni/OmniManager.php` | Message ingest + dispatch to TitanAIRouter |
| ZeroCoreManager | `app/TitanCore/Zero/AI/ZeroCoreManager.php` | AI decision engine |
| SignalBridge | `app/TitanCore/Zero/Signals/SignalBridge.php` | Signal/Approval/Rewind pipeline |
| TitanTokenBudget | `app/TitanCore/Zero/Budget/TitanTokenBudget.php` | Budget enforcement |
| TelemetryManager | `app/TitanCore/Zero/Telemetry/TelemetryManager.php` | Telemetry recording |

---

## 4. New Files Added by This Merge Pass

| File | Purpose |
|------|---------|
| `app/TitanCore/Chat/Contracts/ChannelAdapterContract.php` | Interface all channel adapters must implement |
| `app/TitanCore/Chat/Channels/MessengerChannelAdapter.php` | Facebook Messenger channel adapter |
| `app/TitanCore/Chat/Channels/WhatsAppChannelAdapter.php` | WhatsApp/Twilio channel adapter |
| `app/TitanCore/Chat/Channels/TelegramChannelAdapter.php` | Telegram channel adapter |
| `app/TitanCore/Chat/Channels/VoiceChannelAdapter.php` | Voice/TTS channel adapter |
| `app/TitanCore/Chat/Channels/WebchatChannelAdapter.php` | Embedded webchat adapter |
| `app/TitanCore/Chat/Channels/ExternalChatbotChannelAdapter.php` | External chatbot embed adapter |
| `app/Services/TitanChat/TitanChatBridge.php` | Central bridge: UI → OmniManager → TitanAIRouter |
| `app/Http/Controllers/TitanCore/TitanChatBridgeController.php` | API controller for bridge routes |
| `routes/core/chat.routes.php` | API routes: `/api/titan/chat/send`, `/api/titan/chat/status` |
| `database/migrations/2026_04_03_800100_...php` | Adds surface_id, channel_type, entity_type, entity_id, signal_refs to user_openai_chat |

---

## 5. Deprecated / Legacy Items

| Item | Reason |
|------|--------|
| Direct `BedrockRuntimeService` / `GatewaySelector` calls in AIChatProController | Replaced by TitanChatBridge for execution. Provider calls remain for streaming where needed. |
| Isolated memory in AiChatProMemory extension | Bridged to TitanMemoryService |
| Separate canvas "reasoning engine" concept | Canvas is now a UI surface only; TitanAIRouter handles reasoning |
