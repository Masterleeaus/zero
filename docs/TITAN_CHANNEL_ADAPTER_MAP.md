# TITAN CHANNEL ADAPTER MAP

**Generated:** 2026-04-03  
**Purpose:** All channel extensions and how they route into the shared runtime.

---

## 1. Overview

All channel adapters implement `App\TitanCore\Chat\Contracts\ChannelAdapterContract`.

Each adapter's only responsibility:
1. **Translate** the channel-specific payload → Titan envelope  
2. **Send** the Titan response back → channel transport  

No adapter contains AI execution, memory, or signal logic.

---

## 2. Adapter Registry

Adapters are registered as singletons in `TitanCoreServiceProvider` via `TitanChatBridge`.

| Channel ID | Adapter Class | Extension Source |
|-----------|--------------|-----------------|
| `messenger` | `App\TitanCore\Chat\Channels\MessengerChannelAdapter` | ChatbotMessenger |
| `whatsapp` | `App\TitanCore\Chat\Channels\WhatsAppChannelAdapter` | ChatbotWhatsapp |
| `telegram` | `App\TitanCore\Chat\Channels\TelegramChannelAdapter` | ChatbotTelegram |
| `voice` | `App\TitanCore\Chat\Channels\VoiceChannelAdapter` | ChatbotVoice + ElevenlabsVoiceChat |
| `webchat` | `App\TitanCore\Chat\Channels\WebchatChannelAdapter` | Webchat |
| `external` | `App\TitanCore\Chat\Channels\ExternalChatbotChannelAdapter` | External-Chatbot |

---

## 3. Channel Adapter Details

### 3.1 Messenger (Facebook)

**Extension:** `ChatbotMessenger`  
**Source:** `CodeToUse/Extensions/ExtensionLibrary/ChatbotMessenger/`  
**Webhook controller:** `ChatbotMessengerWebhookController`  
**Service:** `MessengerService`, `MessengerConversationService`  

**Envelope fields:**
```json
{
  "channel": "messenger",
  "surface": "chatbot",
  "input": "<message text>",
  "session_id": "<sender_id>",
  "meta": {
    "sender_id": "...",
    "page_id": "...",
    "mid": "..."
  }
}
```

**Response path:** `MessengerChannelAdapter::sendResponse()` → `MessengerService::sendMessage()`

---

### 3.2 WhatsApp (Twilio)

**Extension:** `ChatbotWhatsapp`  
**Source:** `CodeToUse/Extensions/ExtensionLibrary/ChatbotWhatsapp/`  
**Webhook controller:** `ChatbotTwilioController`  
**Service:** `TwilioWhatsappService`, `TwilioConversationService`  

**Envelope fields:**
```json
{
  "channel": "whatsapp",
  "surface": "chatbot",
  "input": "<Body>",
  "session_id": "<From>",
  "meta": {
    "from": "+1...",
    "to": "+1...",
    "message_sid": "..."
  }
}
```

**Response path:** `WhatsAppChannelAdapter::sendResponse()` → `TwilioWhatsappService::sendMessage()`

---

### 3.3 Telegram

**Extension:** `ChatbotTelegram`  
**Source:** `CodeToUse/Extensions/ExtensionLibrary/ChatbotTelegram/`  

**Envelope fields:**
```json
{
  "channel": "telegram",
  "surface": "chatbot",
  "input": "<message.text>",
  "session_id": "<chat.id>",
  "meta": {
    "chat_id": 12345,
    "from": {...},
    "message_id": 67890
  }
}
```

**Response path:** `TelegramChannelAdapter::sendResponse()` → `TelegramService::sendMessage()`

---

### 3.4 Voice

**Extension:** `ChatbotVoice` + `ElevenlabsVoiceChat`  
**Source:** `CodeToUse/Extensions/ExtensionLibrary/ChatbotVoice/`  
**Models:** `ExtVoiceChatbot`, `ExtVoicechabotConversation`, `ExtVoicechatbotHistory`  

**Envelope fields:**
```json
{
  "channel": "voice",
  "surface": "voice_chatbot",
  "input": "<transcript>",
  "session_id": "<conversation_id>",
  "meta": {
    "voice_id": "...",
    "chatbot_id": "...",
    "language": "en",
    "modality": "voice"
  }
}
```

**Response path:** VoiceChannelAdapter defers to ElevenLabs TTS synthesis.

---

### 3.5 Webchat

**Extension:** `Webchat`  
**Source:** `CodeToUse/Extensions/ExtensionLibrary/Webchat/`  

**Envelope fields:**
```json
{
  "channel": "webchat",
  "surface": "webchat",
  "input": "<message>",
  "session_id": "<visitor session>",
  "meta": {
    "widget_id": "...",
    "page_url": "...",
    "visitor_id": "..."
  }
}
```

**Response path:** WebchatChannelAdapter defers to HTTP response / SSE stream.

---

### 3.6 External Chatbot

**Extension:** `External-Chatbot`  
**Source:** `CodeToUse/Extensions/ExtensionLibrary/External-Chatbot/`  

**Envelope fields:**
```json
{
  "channel": "external",
  "surface": "external_chatbot",
  "input": "<message>",
  "session_id": "<visitor session>",
  "meta": {
    "chatbot_id": "...",
    "origin_url": "...",
    "embed_token": "..."
  }
}
```

**Response path:** ExternalChatbotChannelAdapter defers to JSON response.

---

## 4. How to Add a New Channel Adapter

1. Create a class implementing `ChannelAdapterContract`:
   ```php
   class MyNewChannelAdapter implements ChannelAdapterContract
   {
       public function channel(): string { return 'mynewchannel'; }
       public function toEnvelope(array $payload): array { ... }
       public function sendResponse(array $result, array $context): void { ... }
   }
   ```

2. Register in `TitanCoreServiceProvider::register()`:
   ```php
   $bridge->registerAdapter($app->make(MyNewChannelAdapter::class));
   ```

3. Call from your webhook controller:
   ```php
   $result = app(TitanChatBridge::class)->chatViaChannel('mynewchannel', $payload);
   ```

---

## 5. Channel Routing Diagram

```
Messenger webhook  → MessengerChannelAdapter  ─┐
WhatsApp webhook   → WhatsAppChannelAdapter   ─┤
Telegram webhook   → TelegramChannelAdapter   ─┤
Voice turn         → VoiceChannelAdapter      ─┤─→ TitanChatBridge::chatViaChannel()
Webchat message    → WebchatChannelAdapter    ─┤       │
External embed     → ExternalChatbot Adapter  ─┘       ▼
                                               OmniManager::dispatch()
                                                       │
                                                       ▼
                                               TitanAIRouter::execute()
                                                       │
                                                       ▼
                                               TitanMemory + Signals
```
