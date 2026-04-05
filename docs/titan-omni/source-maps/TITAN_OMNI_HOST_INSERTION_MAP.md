# TITAN OMNI — HOST INSERTION MAP
**Pass:** 01 — Foundation Scan  
**Date:** 2026-04-05  
**Author:** Copilot Agent  
**Status:** Structural freeze — insertion points locked, no migrations created yet

---

## Overview

This document maps every planned insertion point for Titan Omni into the host Zero codebase.
It covers `app/`, `routes/`, `database/`, `resources/`, and `config/` layers.

All insertions follow the doctrine: **reuse → extend → refactor → replace only if unavoidable.**

Tenant boundary: **`company_id`** (sourced from `Auth::user()->company_id` via `BelongsToCompany` trait).

---

## 1. `app/` Insertion Points

### 1.1 New Directory: `app/Models/Omni/`

Create the following Eloquent models (all use `BelongsToCompany` trait):

| Model File | Table | Owns |
|-----------|-------|------|
| `OmniAgent.php` | `omni_agents` | AI agent definition per company (replaces isolated `chatbot` scoping) |
| `OmniConversation.php` | `omni_conversations` | Unified conversation thread (all channels) |
| `OmniMessage.php` | `omni_messages` | Individual message within a conversation |
| `OmniChannelBridge.php` | `omni_channel_bridges` | Per-company channel credential + webhook config |
| `OmniKnowledgeArticle.php` | `omni_knowledge_articles` | Company-scoped knowledge base article |
| `OmniCustomer.php` | `omni_customers` | Omni-side customer identity (linked to CRM `Customer` by nullable FK) |
| `OmniAnalytics.php` | `omni_analytics` | Aggregated conversation metrics per agent/channel/period |

> **Rule:** `OmniCustomer` links to `app/Models/Crm/Customer` via nullable `crm_customer_id`. Do NOT duplicate customer address, notes, or deal data.

---

### 1.2 New Directory: `app/Models/Omni/Campaign/`

| Model File | Table | Owns |
|-----------|-------|------|
| `OmniCampaign.php` | `omni_campaigns` | Multi-channel broadcast campaign |
| `OmniCampaignRecipient.php` | `omni_campaign_recipients` | Per-recipient delivery record |
| `OmniContactList.php` | `omni_contact_lists` | Named list of Omni customers |

> **Note:** These replace `ext_marketing_campaigns` and related MarketingBot tables. The `ext_` tables remain as source during the dual-write window (Pass 03).

---

### 1.3 New Directory: `app/Models/Omni/Voice/`

| Model File | Table | Owns |
|-----------|-------|------|
| `OmniVoiceCall.php` | `omni_voice_calls` | Voice call record (inbound + outbound) |
| `OmniCallLog.php` | `omni_call_logs` | Timestamped call events |
| `OmniCallbackSchedule.php` | `omni_callback_schedules` | Pending callback requests |

> **Do NOT duplicate** `titanhello_calls` tables during Pass 02. Use TitanHello tables directly until a dual-write bridge is ready (Pass 04+).

---

### 1.4 Extend: `app/TitanCore/Omni/OmniManager.php`

**Current state:** Dispatch + ingest via TitanAIRouter + TelemetryManager. No DB persistence.

**Planned extension (Pass 02):**
- Add `persistConversation(array $envelope): OmniConversation` method
- Add `persistMessage(OmniConversation $conv, array $envelope): OmniMessage` method
- Do NOT refactor the existing `dispatch()` or `ingest()` signatures

**Contract:** All channel adapters continue to call `OmniManager::dispatch()`. The manager internally persists before routing.

---

### 1.5 New Directory: `app/Services/Omni/`

| Service File | Responsibility |
|-------------|---------------|
| `OmniConversationService.php` | Create/resolve conversations; handle first-message + follow-up logic |
| `OmniChannelService.php` | Register/deregister channel bridges; validate credentials |
| `OmniCampaignService.php` | Create and schedule broadcast campaigns; track delivery |
| `OmniKnowledgeService.php` | Manage KB articles; provide RAG context to OmniManager |
| `OmniInboxService.php` | Agent-facing inbox: assign conversations, mark resolved |
| `OmniAnalyticsService.php` | Aggregate channel metrics by period/agent/company |

---

### 1.6 New Directory: `app/Services/Omni/Voice/`

| Service File | Responsibility |
|-------------|---------------|
| `BlandAiService.php` | Bland.ai API client (adapted from TitanTalk AIConnect donor) |
| `VapiService.php` | VAPI API client (adapted from TitanTalk AIConnect donor) |
| `TwilioVoiceService.php` | Twilio call + SMS (adapted from TitanHello donor) |
| `VoiceCallOrchestrator.php` | Driver pattern: routes calls to correct provider (Bland/VAPI/Twilio) |

---

### 1.7 New Directory: `app/Http/Controllers/Omni/`

| Controller File | Route Prefix | Responsibility |
|----------------|-------------|----------------|
| `OmniInboxController.php` | `dashboard/omni/inbox` | Agent inbox — list, claim, reply, transfer conversations |
| `OmniConversationController.php` | `dashboard/omni/conversations` | Conversation CRUD + history |
| `OmniAgentController.php` | `dashboard/omni/agents` | Agent config (name, model, instructions, channels) |
| `OmniChannelController.php` | `dashboard/omni/channels` | Channel bridge registration (WhatsApp/Telegram/Voice/Messenger) |
| `OmniCampaignController.php` | `dashboard/omni/campaigns` | Campaign CRUD + launch |
| `OmniKnowledgeController.php` | `dashboard/omni/knowledge` | KB article CRUD |
| `OmniAnalyticsController.php` | `dashboard/omni/analytics` | Channel analytics dashboard |

---

### 1.8 New Directory: `app/Http/Controllers/Omni/Webhooks/`

| Controller File | URI | Notes |
|----------------|-----|-------|
| `WhatsAppWebhookController.php` | `webhooks/omni/whatsapp` | Twilio/Meta → OmniManager dispatch |
| `TelegramWebhookController.php` | `webhooks/omni/telegram` | Telegram → OmniManager dispatch |
| `VoiceWebhookController.php` | `webhooks/omni/voice` | Twilio/VAPI/Bland → OmniManager dispatch |
| `MessengerWebhookController.php` | `webhooks/omni/messenger` | Facebook Messenger → OmniManager dispatch |
| `GenericWebhookController.php` | `webhooks/omni/generic/{channel}` | Fallback webhook receiver |

> **All webhook controllers** must call `OmniManager::dispatch()`, not speak directly to channel services. The channel adapters in `app/TitanCore/Chat/Channels/` handle envelope normalisation.

---

### 1.9 Extend: `app/Providers/TitanCoreServiceProvider.php`

Register new Omni singletons in `register()`:

```php
$this->app->singleton(OmniConversationService::class);
$this->app->singleton(OmniChannelService::class);
$this->app->singleton(OmniCampaignService::class);
$this->app->singleton(OmniKnowledgeService::class);
$this->app->singleton(OmniInboxService::class);
$this->app->singleton(VoiceCallOrchestrator::class);
```

> Do NOT create a separate `OmniServiceProvider` unless the binding count exceeds 20 or config merging is needed. Use existing TitanCoreServiceProvider.

---

### 1.10 New Directory: `app/Events/Omni/`

| Event | Trigger |
|-------|---------|
| `OmniConversationStarted.php` | New conversation opened |
| `OmniMessageReceived.php` | Customer message ingested |
| `OmniMessageSent.php` | Agent/AI reply dispatched |
| `OmniConversationResolved.php` | Conversation closed |
| `OmniConversationTransferred.php` | Reassigned to different agent |
| `OmniCampaignLaunched.php` | Campaign broadcast started |
| `OmniCampaignCompleted.php` | All recipients processed |
| `OmniChannelRegistered.php` | New channel bridge added |
| `OmniChannelDeregistered.php` | Channel bridge removed |
| `OmniVoiceCallStarted.php` | Inbound/outbound voice call opened |
| `OmniVoiceCallEnded.php` | Voice call completed |

> All Omni events must be registered in `app/Providers/EventServiceProvider.php` under the `// MODULE: Omni` block.

---

### 1.11 New Directory: `app/Policies/Omni/`

| Policy | Guards |
|--------|--------|
| `OmniAgentPolicy.php` | viewAny/view/create/update/delete — company scoped |
| `OmniConversationPolicy.php` | viewAny/view/reply/transfer/close — agent role check |
| `OmniCampaignPolicy.php` | viewAny/view/create/update/delete/launch |
| `OmniKnowledgePolicy.php` | viewAny/view/create/update/delete |
| `OmniChannelPolicy.php` | viewAny/view/create/update/delete — admin only |

---

### 1.12 New Jobs: `app/Jobs/Omni/`

| Job | Queue | Trigger |
|-----|-------|---------|
| `ProcessOmniCampaignBatch.php` | `omni` | OmniCampaignLaunched |
| `SendOmniMessage.php` | `omni` | OmniCampaignService batch dispatch |
| `SyncOmniAnalytics.php` | `omni-analytics` | Scheduled (daily) |

---

## 2. `routes/` Insertion Points

### 2.1 New File: `routes/core/omni.routes.php`

```
Route group: web + auth + throttle:120,1
Prefix: dashboard/omni
Name: dashboard.omni.*
```

**Named routes:**

| Name | Method | URI |
|------|--------|-----|
| `dashboard.omni.inbox.index` | GET | `dashboard/omni/inbox` |
| `dashboard.omni.inbox.claim` | POST | `dashboard/omni/inbox/{conversation}/claim` |
| `dashboard.omni.inbox.reply` | POST | `dashboard/omni/inbox/{conversation}/reply` |
| `dashboard.omni.inbox.resolve` | POST | `dashboard/omni/inbox/{conversation}/resolve` |
| `dashboard.omni.inbox.transfer` | POST | `dashboard/omni/inbox/{conversation}/transfer` |
| `dashboard.omni.conversations.index` | GET | `dashboard/omni/conversations` |
| `dashboard.omni.conversations.show` | GET | `dashboard/omni/conversations/{conversation}` |
| `dashboard.omni.agents.index` | GET | `dashboard/omni/agents` |
| `dashboard.omni.agents.create` | GET | `dashboard/omni/agents/create` |
| `dashboard.omni.agents.store` | POST | `dashboard/omni/agents` |
| `dashboard.omni.agents.edit` | GET | `dashboard/omni/agents/{agent}/edit` |
| `dashboard.omni.agents.update` | PUT | `dashboard/omni/agents/{agent}` |
| `dashboard.omni.agents.destroy` | DELETE | `dashboard/omni/agents/{agent}` |
| `dashboard.omni.channels.index` | GET | `dashboard/omni/channels` |
| `dashboard.omni.channels.store` | POST | `dashboard/omni/channels` |
| `dashboard.omni.channels.update` | PUT | `dashboard/omni/channels/{bridge}` |
| `dashboard.omni.channels.destroy` | DELETE | `dashboard/omni/channels/{bridge}` |
| `dashboard.omni.campaigns.index` | GET | `dashboard/omni/campaigns` |
| `dashboard.omni.campaigns.create` | GET | `dashboard/omni/campaigns/create` |
| `dashboard.omni.campaigns.store` | POST | `dashboard/omni/campaigns` |
| `dashboard.omni.campaigns.show` | GET | `dashboard/omni/campaigns/{campaign}` |
| `dashboard.omni.campaigns.launch` | POST | `dashboard/omni/campaigns/{campaign}/launch` |
| `dashboard.omni.knowledge.index` | GET | `dashboard/omni/knowledge` |
| `dashboard.omni.knowledge.create` | GET | `dashboard/omni/knowledge/create` |
| `dashboard.omni.knowledge.store` | POST | `dashboard/omni/knowledge` |
| `dashboard.omni.knowledge.edit` | GET | `dashboard/omni/knowledge/{article}/edit` |
| `dashboard.omni.knowledge.update` | PUT | `dashboard/omni/knowledge/{article}` |
| `dashboard.omni.knowledge.destroy` | DELETE | `dashboard/omni/knowledge/{article}` |
| `dashboard.omni.analytics.index` | GET | `dashboard/omni/analytics` |

### 2.2 New File: `routes/core/omni_webhooks.routes.php`

```
Route group: web (no auth) + signed or webhook-secret middleware
Prefix: webhooks/omni
Name: webhooks.omni.*
```

| Name | Method | URI |
|------|--------|-----|
| `webhooks.omni.whatsapp` | POST | `webhooks/omni/whatsapp` |
| `webhooks.omni.whatsapp.verify` | GET | `webhooks/omni/whatsapp` |
| `webhooks.omni.telegram` | POST | `webhooks/omni/telegram` |
| `webhooks.omni.voice` | POST | `webhooks/omni/voice` |
| `webhooks.omni.messenger` | POST | `webhooks/omni/messenger` |
| `webhooks.omni.messenger.verify` | GET | `webhooks/omni/messenger` |
| `webhooks.omni.generic` | POST | `webhooks/omni/generic/{channel}` |

### 2.3 Extend: `app/Providers/RouteServiceProvider.php`

Add `omni.routes.php` to `loadCoreRoutes()` call list.  
Add `omni_webhooks.routes.php` to webhook route loading (outside auth group).

---

## 3. `database/` Insertion Points

> **Note:** This pass does NOT create migrations. Pass 02 will create them.

### 3.1 Planned Migration: `2026_04_05_900100_create_omni_core_tables.php`

| Table | Key Columns |
|-------|------------|
| `omni_agents` | id, uuid, company_id, user_id, name, role, model, avatar_url, instructions, system_prompt, tone, language, is_active, metadata |
| `omni_conversations` | id, uuid, company_id, agent_id, omni_customer_id, session_id, channel_type, channel_id, external_conversation_id, status, assigned_to, started_at, resolved_at |
| `omni_messages` | id, uuid, conversation_id, company_id, direction, content_type, content, sender_type, sender_id, delivered_at, read_at, metadata |
| `omni_channel_bridges` | id, company_id, agent_id, channel_type, credentials(json), webhook_url, is_active, verified_at |
| `omni_customers` | id, uuid, company_id, crm_customer_id (nullable FK), name, email, phone, channel_identities(json) |
| `omni_knowledge_articles` | id, uuid, company_id, agent_id, title, content, embedding_model, status |

### 3.2 Planned Migration: `2026_04_05_900200_create_omni_campaign_tables.php`

| Table | Key Columns |
|-------|------------|
| `omni_campaigns` | id, uuid, company_id, name, channel_type, content, status, scheduled_at, launched_at, completed_at |
| `omni_campaign_recipients` | id, campaign_id, omni_customer_id, status, sent_at, delivered_at, failed_at, failure_reason |
| `omni_contact_lists` | id, company_id, name, description |
| `omni_contact_list_members` | id, contact_list_id, omni_customer_id |

### 3.3 Planned Migration: `2026_04_05_900300_create_omni_voice_tables.php`

| Table | Key Columns |
|-------|------------|
| `omni_voice_calls` | id, uuid, company_id, conversation_id, direction, provider, provider_call_id, from, to, status, duration_seconds, recording_url, started_at, ended_at |
| `omni_call_logs` | id, voice_call_id, event_type, payload(json), occurred_at |
| `omni_callback_schedules` | id, company_id, omni_customer_id, scheduled_at, handled_at, notes |

### 3.4 Planned Migration: `2026_04_05_900400_create_omni_analytics_table.php`

| Table | Key Columns |
|-------|------------|
| `omni_analytics` | id, company_id, agent_id, channel_type, period_date, conversations_opened, conversations_resolved, avg_response_time_seconds, messages_sent, messages_received |

### 3.5 Planned Migration: `2026_04_05_900500_add_company_id_to_chatbot_tables.php`

Adds `company_id` to existing host tables that are missing it:
- `chatbot` table (already has `user_id` since 2024_02_28 migration; `company_id` column missing)
- `chatbot_data_vectors` table

---

## 4. `resources/` Insertion Points

### 4.1 Views: `resources/views/default/panel/user/omni/`

| View Path | Blade Template |
|-----------|---------------|
| `omni/inbox/index.blade.php` | Agent inbox — conversation list |
| `omni/inbox/conversation.blade.php` | Single conversation thread |
| `omni/agents/index.blade.php` | Agent list |
| `omni/agents/create.blade.php` | New agent form |
| `omni/agents/edit.blade.php` | Edit agent |
| `omni/channels/index.blade.php` | Channel bridge list |
| `omni/channels/create.blade.php` | New channel form (type-switcher: WhatsApp/Telegram/Voice/Messenger) |
| `omni/campaigns/index.blade.php` | Campaign list |
| `omni/campaigns/create.blade.php` | New campaign form |
| `omni/campaigns/show.blade.php` | Campaign detail + delivery stats |
| `omni/knowledge/index.blade.php` | KB article list |
| `omni/knowledge/create.blade.php` | New article form |
| `omni/knowledge/edit.blade.php` | Edit article |
| `omni/analytics/index.blade.php` | Channel analytics dashboard |

> All views must use the host `default/panel` layout system. No parallel UI trees.

---

## 5. `config/` Insertion Points

### 5.1 New File: `config/titan_omni.php`

```php
return [
    'enabled'          => env('TITAN_OMNI_ENABLED', false),
    'default_channel'  => env('TITAN_OMNI_DEFAULT_CHANNEL', 'webchat'),
    'voice_provider'   => env('TITAN_OMNI_VOICE_PROVIDER', 'vapi'),   // vapi | bland | twilio
    'whatsapp_driver'  => env('TITAN_OMNI_WHATSAPP_DRIVER', 'twilio'), // twilio | meta
    'telegram_token'   => env('TITAN_OMNI_TELEGRAM_TOKEN'),
    'bland_api_key'    => env('BLAND_AI_API_KEY'),
    'vapi_api_key'     => env('VAPI_API_KEY'),
    'twilio_sid'       => env('TWILIO_ACCOUNT_SID'),
    'twilio_token'     => env('TWILIO_AUTH_TOKEN'),
    'twilio_from'      => env('TWILIO_FROM_NUMBER'),
    'meta_app_id'      => env('META_APP_ID'),
    'meta_app_secret'  => env('META_APP_SECRET'),
    'meta_verify_token'=> env('META_WEBHOOK_VERIFY_TOKEN'),
    'analytics_retention_days' => env('TITAN_OMNI_ANALYTICS_RETENTION', 90),
    'queues' => [
        'dispatch'  => env('TITAN_OMNI_QUEUE_DISPATCH', 'omni'),
        'analytics' => env('TITAN_OMNI_QUEUE_ANALYTICS', 'omni-analytics'),
    ],
];
```

> Register via `TitanCoreServiceProvider::register()` → `$this->mergeConfigFrom(base_path('config/titan_omni.php'), 'titan_omni');`

---

## 6. Load Order Requirements

Pass 01 confirms the following load order for subsequent passes:

```
Pass 01 → Architecture freeze (this document)
Pass 02 → Migrations + Models (omni_* tables, OmniAgent/OmniConversation/OmniMessage)
Pass 03 → Services + OmniManager extension (persistence layer)
Pass 04 → Controllers + Routes (inbox, agents, channels)
Pass 05 → Webhook controllers + channel adapter wiring
Pass 06 → Campaign system (OmniCampaign + MarketingBot extraction)
Pass 07 → Voice layer (TitanHello + VoiceCallOrchestrator)
Pass 08 → Knowledge base unification (ChatbotData → OmniKnowledgeArticle)
Pass 09 → Analytics layer
Pass 10 → Dual-write bridge teardown (ext_* table deprecation)
```

---

## 7. What Must NOT Be Created

| Thing | Reason |
|-------|--------|
| Parallel `users` table or auth system | Host auth is canonical |
| Parallel `companies` or `teams` tables | Host tenancy is canonical |
| Parallel `customers` table | CRM Customer model is canonical — Omni bridges via `crm_customer_id` |
| Parallel `invoices` / `quotes` | Finance owns these |
| Parallel `service_jobs` | Work/FSM owns these |
| New `ServiceProvider` for Omni | Use TitanCoreServiceProvider unless binding count > 20 |
| Independent notification system | Host uses Laravel notifications; Omni fires events |

---

## 8. Validation Checklist (Pre-Pass 02)

- [ ] `app/TitanCore/Omni/OmniManager.php` reviewed and unchanged
- [ ] `app/TitanCore/Chat/Channels/` adapters reviewed — no changes needed for Pass 02
- [ ] `app/Providers/TitanCoreServiceProvider.php` reviewed — extension points identified
- [ ] `routes/core/chat.routes.php` reviewed — existing titan.chat.* routes unchanged
- [ ] Migration number space confirmed: `900100–900500` available
- [ ] `config/titan_omni.php` stub ready
- [ ] `EventServiceProvider` has slot for Omni events
