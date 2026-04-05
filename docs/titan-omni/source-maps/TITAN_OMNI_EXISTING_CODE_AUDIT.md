# TITAN OMNI — EXISTING CODE AUDIT
**Pass:** 01 — Foundation Scan  
**Date:** 2026-04-05  
**Author:** Copilot Agent  
**Status:** Complete — structural freeze only, no migrations created

---

## 1. Scope of Scan

This audit covers all comms-related code found across:

| Area | Scan Target |
|------|-------------|
| Host app | `app/`, `routes/`, `database/migrations/`, `config/`, `resources/` |
| Donor code | `CodeToUse/Omni/`, `CodeToUse/Comms/` |
| Existing TitanCore | `app/TitanCore/Omni/`, `app/TitanCore/Chat/` |
| Docs | `docs/COMMS_TRIAGE_PLAN.md`, `docs/zero/`, `docs/DOC_INDEX.md` |

---

## 2. Host — Existing Comms Code Inventory

### 2.1 Models

| Model | Namespace | Table | company_id | Notes |
|-------|-----------|-------|-----------|-------|
| `Chatbot` | `App\Models\Chatbot\Chatbot` | `chatbot` | ✅ via trait | Base chatbot agent — title, role, model, instructions |
| `ChatbotData` | `App\Models\Chatbot\ChatbotData` | `chatbot_data` | ✅ via trait | Training corpus items |
| `ChatbotDataVector` | `App\Models\Chatbot\ChatbotDataVector` | `chatbot_data_vectors` | ❌ | Embedding vectors |
| `Domain` | `App\Models\Chatbot\Domain` | unknown | ❌ | Chatbot domain allowlist |
| `ElevenlabVoice` | `App\Models\Voice\ElevenlabVoice` | `elevenlab_voices` | ❌ | Voice library per user |

### 2.2 TitanCore Omni Layer

| File | Path | Purpose |
|------|------|---------|
| `OmniManager` | `app/TitanCore/Omni/OmniManager.php` | Message ingest + dispatch through TitanAIRouter |
| `ChannelAdapterContract` | `app/TitanCore/Chat/Contracts/ChannelAdapterContract.php` | Interface for all channel adapters |
| `WhatsAppChannelAdapter` | `app/TitanCore/Chat/Channels/WhatsAppChannelAdapter.php` | Twilio/WhatsApp → Titan envelope |
| `TelegramChannelAdapter` | `app/TitanCore/Chat/Channels/TelegramChannelAdapter.php` | Telegram webhook → Titan envelope |
| `VoiceChannelAdapter` | `app/TitanCore/Chat/Channels/VoiceChannelAdapter.php` | Voice call → Titan envelope |
| `MessengerChannelAdapter` | `app/TitanCore/Chat/Channels/MessengerChannelAdapter.php` | Facebook Messenger → Titan envelope |
| `ExternalChatbotChannelAdapter` | `app/TitanCore/Chat/Channels/ExternalChatbotChannelAdapter.php` | Embedded chatbot → Titan envelope |
| `WebchatChannelAdapter` | `app/TitanCore/Chat/Channels/WebchatChannelAdapter.php` | Web session → Titan envelope |

> **Critical observation:** Channel adapters exist and translate webhook payloads to Titan envelopes, but there is **no persistent conversation or message model in the host**. Envelopes are dispatched through `OmniManager → TitanAIRouter` with no DB write-back yet.

### 2.3 Services

| Service | Path | Purpose |
|---------|------|---------|
| `TitanChatBridge` | `app/Services/TitanChat/TitanChatBridge.php` | Bridges chat surfaces to OmniManager |
| `ParserService` | `app/Services/Chatbot/ParserService.php` | Parses training content (text/PDF/URL) |
| `ParserExcelService` | `app/Services/Chatbot/ParserExcelService.php` | Parses Excel training files |
| `LinkCrawler` | `app/Services/Chatbot/LinkCrawler.php` | Crawls URLs for chatbot training |

### 2.4 Controllers

| Controller | Path | Routes prefix |
|-----------|------|---------------|
| `TitanChatBridgeController` | `app/Http/Controllers/TitanCore/TitanChatBridgeController.php` | `api/titan/chat` |
| `ChatbotController` | `app/Http/Controllers/Chatbot/ChatbotController.php` | panel chatbot |
| `ChatbotTrainingController` | `app/Http/Controllers/Chatbot/ChatbotTrainingController.php` | panel chatbot training |
| `ChatbotEmbedController` | `app/Http/Controllers/Chatbot/ChatbotEmbedController.php` | embed script endpoint |
| `ChatbotTokenController` | `app/Http/Controllers/Chatbot/ChatbotTokenController.php` | token endpoint |
| `ChatbotAssetsController` | `app/Http/Controllers/Chatbot/ChatbotAssetsController.php` | asset serving |
| `AiChatbotModelController` | `app/Http/Controllers/AiChatbotModelController.php` | AI model selection |

### 2.5 Routes

| Route File | Prefix / Notes |
|-----------|----------------|
| `routes/core/chat.routes.php` | `api/titan/chat.*` — send + status, auth:sanctum |
| `routes/core/social.routes.php` | Social media extension routes (Facebook/Instagram/LinkedIn/TikTok/X) |
| `routes/panel.php` | Main panel (chatbot routes registered via extension providers) |
| `routes/webhooks.php` | Webhook receivers (payment providers; chatbot webhooks via extension) |

### 2.6 Migrations — Chatbot Tables

| Migration | Table(s) Created/Modified |
|-----------|--------------------------|
| `2024_01_26_110443_add_chatbot_table.php` | `chatbot` (no company_id) |
| `2024_01_29_081000_chatbot_settings.php` | chatbot settings |
| `2024_01_29_150757_chatbot_message_data.php` | message data |
| `2024_01_30_081601_chatbot_chat_data.php` | chat data |
| `2024_01_30_084904_chatbot_history.php` | chat history |
| `2024_02_21_163100_add_chatbot_interests_to_chatbot_table.php` | extends chatbot |
| `2024_02_21_180426_add_status_to_chatbot_table.php` | status column |
| `2024_02_22_065844_create_chatbot_data_table.php` | `chatbot_data` |
| `2024_02_22_120600_create_chatbot_data_vectors_table.php` | `chatbot_data_vectors` |
| `2024_02_28_130323_add_user_id_to_chatbot_table.php` | user_id on chatbot |
| `2025_03_18_050755_create_channel_settings_table.php` | `frontend_channel_settings` (FB/X/IG/LinkedIn display) |

### 2.7 Migrations — Voice Tables

| Migration | Table(s) Created/Modified |
|-----------|--------------------------|
| `2024_02_16_163955_create_elevenlab_voices_table.php` | `elevenlab_voices` |
| `2024_02_19_172005_add_voice_clone_settings_table.php` | voice clone settings |
| `2024_02_19_175115_add_user_id_to_elevenlab_voices_table.php` | user_id column |
| `2024_12_21_080333_add_allowed_voice_count.php` | plan limits |

### 2.8 Config

| Key | File | Notes |
|-----|------|-------|
| `workcore.teamchat` | `config/workcore.php` | `false` — deferred (not tested) |
| `workcore.knowledgebase` | `config/workcore.php` | `true` — enabled |
| `workcore.notices` | `config/workcore.php` | `true` — enabled |

### 2.9 Feature Flags Summary

```
teamchat      → false  (no tables, no routes, no controllers)
knowledgebase → true   (chatbot training data acts as KB; no standalone KB controller)
notices       → true   (notice board scoped to company)
```

---

## 3. Donor Code Assessment

### 3.1 MarketingBot (`CodeToUse/Omni/TitanOmni/Titan Omni/MarketingBot/`)

**Type:** Laravel extension (namespace `App\Extensions\MarketingBot\*`)  
**Status:** Donor — not integrated

| Component | Assessment |
|-----------|-----------|
| **Tables:** `ext_marketing_campaigns`, `ext_marketing_conversations`, `ext_marketing_message_histories`, `ext_telegram_bots`, `ext_telegram_groups`, `ext_telegram_contacts`, `ext_telegram_group_subscribers`, `ext_whatsapp_channels`, `contacts`, `segments`, `contact_lists` | Keep `ext_` prefix tables. **Missing `company_id` on most tables** — must be added during Pass 02 migration work. |
| **Models:** MarketingCampaign, MarketingConversation, MarketingMessageHistory, TelegramBot, TelegramGroup, TelegramGroupSubscriber, TelegramContact, WhatsappChannel, Contact, ContactList, Segment | Reusable. Need `company_id` scope added + BelongsToCompany trait. |
| **Controllers:** Campaign (WhatsApp/Telegram), Inbox, Settings, Webhook, ContactList, Segment | Reusable. Need host auth + company scope. |
| **Services:** Telegram send, WhatsApp send, AI embed + generate, campaign runner | Reusable. Need decoupled from extension-specific infra. |
| **Console Commands:** RunTelegramCampaignCommand, RunWhatsappCampaignCommand | Reusable as scheduled jobs. |
| **Enums:** CampaignStatus, CampaignType, EmbeddingTypeEnum | Reusable as-is. |
| **Risk:** `contacts` table name conflicts with possible host CRM contact tables. Must map → `omni_contacts` or bridge to `crm_customers`. |

**Recommendation:** Extract feature-specific logic (campaign, conversation, delivery). Bridge Contact/Segment to host CRM `Customer` model.

---

### 3.2 TitanHello (`CodeToUse/Omni/TitanOmni/Titan Omni/TitanHello/`)

**Type:** Laravel extension (namespaced `TitanHello\*`)  
**Status:** Donor — not integrated. Most complete voice system.

| Component | Assessment |
|-----------|-----------|
| **Tables:** `titanhello_calls`, `titanhello_call_events`, `titanhello_call_recordings`, `titanhello_ring_groups`, `titanhello_ivr_menus`, `titanhello_ivr_options`, `titanhello_inbound_numbers`, `titanhello_dial_campaigns`, `titanhello_dial_campaign_contacts`, `titanhello_callback_requests` | Clean table namespace. Has dedicated `2026_03_02_090300_add_company_id_to_titanhello_tables.php` migration. |
| **Models:** (inferred from migrations) TitanHelloCall, CallEvent, CallRecording, RingGroup, IvrMenu, InboundNumber, DialCampaign, CallbackRequest | Need `BelongsToCompany` trait added. |
| **Controllers:** ChatbotVoiceController, AvatarController, ChatbotVoiceHistoryController, ChatbotVoiceTrainController, InternalEventsController, SettingsController, TwilioWebhookController | Reusable with host auth adaptation. |
| **Console Commands:** PruneRecordingsCommand | Reusable. |
| **Voice Providers:** Bland.ai (Agents/Calls/Inbound/Knowledgebase/Prompts/Tools/Voices), VAPI (Assistants/Calls/Files/Knowledgebase/Phone Numbers/Tools/Voices) | Reusable. Abstract behind driver interface. |

**Recommendation:** Adopt `titanhello_*` table namespace. Add BelongsToCompany. Register VoiceChannelAdapter to route inbound calls into OmniManager.

---

### 3.3 TitanTalk (`CodeToUse/Omni/TitanOmni/Titan Omni/TitanTalk/`)

**Type:** CodeIgniter application (AIConnect) — NOT Laravel  
**Status:** Reference only — logic to be adapted, not ported verbatim

| Component | Assessment |
|-----------|-----------|
| `AIConnect/` | CodeIgniter app with Bland.ai + VAPI library wrappers. |
| `libraries/bland_ai/`, `libraries/vapi_ai/` | **Reusable as API client logic.** Should be ported to Laravel service classes under `app/Services/Omni/Voice/`. |
| `Call_logs_model.php`, `Call_logs.php` controller | Schema pattern reference for call log structure. |
| Documentation (`TitanHello/Docs/`) | Design reference. |

**Recommendation:** Extract Bland.ai + VAPI HTTP client logic into `app/Services/Omni/Voice/BlandAiService.php` and `app/Services/Omni/Voice/VapiService.php`.

---

### 3.4 Unified Titan Omni Docs (`CodeToUse/Omni/TitanOmni/Titan Omni/unified Titan Omni/`)

**Type:** Architecture documentation + reference controller stubs  
**Status:** Design source

| File | Role |
|------|------|
| `Docs/TITAN_OMNI_CORE_INTEGRATION_GUIDE.md` | Proposes unified `omni_agents`, `omni_conversations`, `omni_messages` schema |
| `Docs/TITAN_OMNI_IMPLEMENTATION_ROADMAP.md` | Pass sequence for integration |
| `TitanOmniDocs/CHATBOT_EXTENSION_DEEP_SCAN.md` | Deep scan of existing Chatbot extension |
| `TitanOmniDocs/SINGLE_DATABASE_ARCHITECTURE.md` | Single-DB multi-tenant strategy |
| `TitanOmniDocs/ChatbotAgentController.php` | Reference controller stub |
| `TitanOmniDocs/ChatbotConversationPolicy.php` | Reference policy stub |
| `TitanOmniDocs/migration-chatbot-scoping.php` | Reference migration with company_id |
| `TitanOmniDocs/routes-chatbot.php` | Reference routes |

**Recommendation:** Use these as design inputs for Pass 02 schema and Pass 03 controller work.

---

### 3.5 Voice/TitanOmni_SystemOnly_Pass26 (`CodeToUse/Omni/TitanOmni/Titan Omni/voice/Voice/TitanOmni_SystemOnly_Pass26/`)

**Type:** The most complete Chatbot + Omni donor — full Laravel extension  
**Status:** Donor — not integrated. Highest priority for extraction.

| Component | Assessment |
|-----------|-----------|
| **Tables proposed:** `omni_agents`, `omni_conversations`, `omni_messages`, `omni_customers`, `omni_channel_bridges`, `omni_knowledge_articles`, `omni_voice_calls`, `omni_analytics`, `voice_command_logs`, `call_logs`, `callback_schedules` | Use these as canonical Omni table names for Pass 02. |
| **Extension Chatbot System:** Models (Chatbot, ChatbotAvatar, ChatbotCannedResponse, ChatbotChannel, ChatbotChannelWebhook, ChatbotConversation, ChatbotCustomer, ChatbotEmbedding, ChatbotHistory, ChatbotKnowledgeBaseArticle, ChatbotTicket) | Rich conversation model. Bridge `company_id` from parent Chatbot. |
| **Webhook Controllers:** WhatsApp, Telegram, Messenger, Voice, Generic | These complement existing host channel adapters. |
| **Overlay Controllers:** ChatbotAgentController (agent inbox), ChatbotCommandController (internal ops) | Form basis of Omni Inbox UI layer. |
| **KnowledgeBase:** ChatbotKnowledgeBaseArticleController, ChatbotKnowledgeBaseArticleRequest | Connects to `workcore.knowledgebase` flag. |
| **Analytics:** omni_analytics table + ChatbotAnalyticsController | Omni-owned analytics subsystem. |
| **Routes (panel.php):** Full route set for chatbot + omni surfaces | Needs adaptation to `routes/core/omni.routes.php` pattern. |
| **Docs:** PASS23-26 merge plans, provider binding guides, dual-write strategy | Must be read before Pass 02. |

**Recommendation:** This is the primary donor for Pass 02. Extract the omni_* table schema and Chatbot extension model layer. Adapt to host `BelongsToCompany` + `company_id` tenancy.

---

### 3.6 SocialMedia (`CodeToUse/Comms/SocialMedia/`)

**Type:** Laravel extension (namespace `App\Extensions\SocialMedia\*`)  
**Status:** Partially integrated — already referenced in `routes/core/social.routes.php`

| Component | Assessment |
|-----------|-----------|
| **Tables:** `social_media_platforms`, `social_media_campaigns`, `social_media_posts`, `social_media_shared_logs` | Active in host routes. Separate from Omni scope. |
| **Models:** SocialMediaCampaign, SocialMediaPlatform, SocialMediaPost, SocialMediaSharedLog | Social publishing — not direct messaging. |
| **Helpers:** Facebook, Instagram, LinkedIn, TikTok, X | OAuth + API helpers. |
| **Services:** Publisher driver (per-platform), Token refresh (LinkedIn, X), FileSplitService, SocialMediaShareService | Reusable. |

**Assessment for Omni:** Social Media is **publishing/scheduling**, not inbound messaging or conversations. It stays in `app/Extensions/SocialMedia/`. Omni does not own social publishing. Omni **may** receive social replies if a webhook is wired (future scope).

---

### 3.7 ChattingModule (`CodeToUse/Comms/comms/ChattingModule/`)

**Type:** Laravel module (Nwidart-style modular app)  
**Status:** Donor — not integrated

| Component | Assessment |
|-----------|-----------|
| **Tables:** `channel_lists`, `channel_users`, `channel_conversations`, `conversation_files` | Real-time internal team chat. Has `2026_03_02_090259_add_company_id_to_chattingmodule_tables.php`. |
| **Entities:** ChannelList, ChannelUser, ChannelConversation, ConversationFile | Team chat structure. |
| **Service:** `Services/Ai/TitanZeroBridge.php` | Already has a Zero integration point. |
| **Traits:** CompanyScoped, ChattingTrait | Company scoping already present. |

**Assessment for Omni:** ChattingModule covers **internal team chat** (`workcore.teamchat`). This is related to Omni but distinct from customer-facing channels. Pass sequence: enable after Omni conversation core is stable.

---

### 3.8 ably-archive (`CodeToUse/Comms/ably-archive/`)

**Type:** Vendor library archive  
**Status:** Reference only — not a donor module  
**Assessment:** Contains SVG sanitiser + laravel-setting packages. Not relevant to Omni channel logic.

---

## 4. Host Comms Feature Flags

```php
// config/workcore.php
'teamchat'      => false,   // Omni Pass sequence: after conversation core
'knowledgebase' => true,    // ChatbotData currently serves as KB corpus
'notices'       => true,    // Not Omni scope
```

---

## 5. Gaps Identified

| Gap | Impact | Pass |
|-----|--------|------|
| No `omni_conversations` or `omni_messages` table in host | Critical — no conversation persistence | Pass 02 |
| No `company_id` on `chatbot` base table | High — breaks tenant isolation for existing chatbots | Pass 02 (migration required) |
| No `omni_agents` table | High — agent configuration not unified | Pass 02 |
| No `omni_channel_bridges` table | High — webhook channel credentials not persisted | Pass 02 |
| No `omni_knowledge_articles` table | Medium — KB served from `chatbot_data`, not unified | Pass 03 |
| OmniManager dispatches but does not persist conversations | Critical — delivery evidence absent | Pass 02 |
| Channel adapters exist but no webhook registration mechanism | High — webhooks must be registered per company | Pass 02 |
| MarketingCampaign models lack `company_id` | High — multi-tenant isolation broken | Pass 02 |
| TitanHello voice system not connected to OmniManager | Medium — voice calls routed separately | Pass 03 |
| Social replies (inbound from Facebook/Instagram) not wired | Low — social publishing wired, not listening | Future pass |

---

## 6. Summary

**Total donor files assessed:** ~4,095 (Omni), ~5,009 (Comms)  
**Host comms files found:** 30+ controllers/models/services/routes  
**Tables already in host (comms-related):** 12  
**Tables proposed by donor code for Omni:** 11 core omni tables + 10 TitanHello tables  
**Insertion pass ready:** Yes — see TITAN_OMNI_HOST_INSERTION_MAP.md
