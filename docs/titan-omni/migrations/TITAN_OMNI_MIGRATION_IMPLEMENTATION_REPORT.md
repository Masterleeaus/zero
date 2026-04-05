# TITAN OMNI — MIGRATION IMPLEMENTATION REPORT
**Pass:** 02 — Schema + Migration Layer  
**Date:** 2026-04-05  
**Author:** Copilot Agent  
**Status:** COMPLETE

---

## 1. What Was Scanned

### Docs Scanned
- `docs/titan-omni/alignment/TITAN_OMNI_OWNERSHIP_FREEZE.md` — ownership boundaries, immutability contracts, security rules
- `docs/titan-omni/source-maps/TITAN_OMNI_HOST_INSERTION_MAP.md` — planned table list, column specs, migration numbering

### CodeToUse Scanned
- `CodeToUse/Voice/TitanOmni Complete Pass26 HARDENED/titan_omni_complete/database/migrations/` — donor migrations for all core Omni tables
- `CodeToUse/Omni/TitanOmni/` — TitanHello voice model references (column naming patterns)

### Host Code Scanned
- `database/migrations/2026_03_30_123500_add_company_to_ai_tables.php` — confirmed `company_id` already added to `chatbot`, `chatbot_data`, `chatbot_data_vectors` tables (no Pass 02 action needed)
- `app/Models/Concerns/BelongsToCompany.php` — trait confirmed available for all Omni models
- `app/Providers/TitanCoreServiceProvider.php` — `register()` pattern for config merges and singletons
- `app/Providers/EventServiceProvider.php` — event registration format and existing module blocks
- `app/Events/Finance/` — event class naming and constructor pattern

---

## 2. Donor Migration Code Reused

| Host Migration | Donor Source | Notes |
|---------------|-------------|-------|
| `omni_agents` schema | `2026_03_25_000001_create_omni_agents_table.php` | Added `uuid`, `role`, `model`, `avatar_url`, `instructions`, `system_prompt`, `tone` columns per Ownership Freeze spec |
| `omni_customers` schema | `2026_03_25_000002_create_omni_customers_table.php` | Added `uuid`, `crm_customer_id`, `channel_identities` per Ownership Freeze link table spec |
| `omni_conversations` schema | `2026_03_25_000003_create_omni_conversations_table.php` | Extended with `omni_customer_id`, `crm_customer_id`, `linked_job_id`, `linked_invoice_id`, `started_at`, `resolved_at` per ownership freeze |
| `omni_messages` schema | `2026_03_25_000004_create_omni_messages_table.php` | Extended with `company_id`, `direction`, `content_type`, `sender_type`, `sender_id`, `delivered_at`, `failed_at`, `failure_reason` per immutability contract |
| `omni_knowledge_articles` schema | `2026_03_25_000005_create_omni_knowledge_articles_table.php` | Added `uuid`, `embedding_model`, `status` |
| `omni_channel_bridges` schema | `2026_03_25_000006_create_omni_channel_bridges_table.php` | Renamed `channel` → `channel_type`, `bridge_key`/`bridge_secret` → `credentials`/`webhook_secret` for security clarity |
| `omni_voice_calls` schema | `2026_03_25_000007_create_omni_voice_calls_table.php` | Added `uuid`, `omni_customer_id`, `direction`, `provider` columns |
| `omni_analytics` schema | `2026_03_25_000008_create_omni_analytics_table.php` | Expanded to columnar metrics (conversations_opened/resolved, messages, voice_calls, campaigns) instead of generic metric_key/value |
| `omni_audit_logs` concept | `2026_03_30_create_omni_audit_logs.php` | Deferred — host has `tz_audit_log` table (800100 migration); Omni will use it in Pass 09 |
| Campaign tables | `CodeToUse/Voice/TitanOmni Complete Pass26 HARDENED` campaign structures | `omni_campaigns`, `omni_campaign_recipients`, `omni_contact_lists`, `omni_contact_list_members` |
| Call logs / callback schedules | Donor call event patterns | `omni_call_logs`, `omni_callback_schedules` |

---

## 3. Host Code Extended

| Host File | Change | Reason |
|-----------|--------|--------|
| `app/Providers/TitanCoreServiceProvider.php` | Added `mergeConfigFrom('config/titan_omni.php', 'titan_omni')` | Load Omni channel config into app container alongside titan_core/titan_ai |
| `app/Providers/EventServiceProvider.php` | Registered 11 Omni events under `MODULE: Omni` block | All Omni events registered per ownership freeze (Pass 01) requirement |

### Host Code NOT Touched
- `chatbot` / `chatbot_data` / `chatbot_data_vectors` tables — `company_id` already added by `2026_03_30_123500` migration — no Pass 02 action needed
- CRM `customers`, `customer_contacts` tables — read-only from Omni; no schema changes
- `service_jobs` / FSM tables — read-only from Omni; no schema changes
- Finance tables — read-only from Omni; no schema changes

---

## 4. Migration Files Created

| File | Tables Created | Number |
|------|---------------|--------|
| `2026_04_05_900100_create_omni_core_tables.php` | `omni_agents`, `omni_customers`, `omni_conversations`, `omni_messages`, `omni_channel_bridges`, `omni_knowledge_articles` | 900100 |
| `2026_04_05_900200_create_omni_campaign_tables.php` | `omni_contact_lists`, `omni_contact_list_members`, `omni_campaigns`, `omni_campaign_recipients` | 900200 |
| `2026_04_05_900300_create_omni_voice_tables.php` | `omni_voice_calls`, `omni_call_logs`, `omni_callback_schedules` | 900300 |
| `2026_04_05_900400_create_omni_analytics_table.php` | `omni_analytics` | 900400 |

**Total tables created: 14**

---

## 5. Every Table Created

| Table | Migration | Key Design Notes |
|-------|-----------|-----------------|
| `omni_agents` | 900100 | `uuid`, `company_id`, `user_id` (nullable), `slug` (unique), `is_active`, full AI config columns |
| `omni_customers` | 900100 | `uuid`, `company_id`, `crm_customer_id` (nullable FK bridge), `channel_identities` JSON |
| `omni_conversations` | 900100 | `uuid`, `company_id`, `agent_id`, host link FKs (crm_customer_id/linked_job_id/linked_invoice_id nullable), `resolved_at` set-once |
| `omni_messages` | 900100 | `uuid`, `company_id`, NO `updated_at` (immutable), `delivered_at`/`read_at`/`failed_at` set-once |
| `omni_channel_bridges` | 900100 | `company_id`, `credentials` (encrypted at rest by app layer), `webhook_secret` hidden |
| `omni_knowledge_articles` | 900100 | `uuid`, `company_id`, `agent_id`, `embedding_model`, `status` |
| `omni_contact_lists` | 900200 | `company_id`, `member_count` counter-cache |
| `omni_contact_list_members` | 900200 | Pivot: `contact_list_id` + `omni_customer_id` unique pair |
| `omni_campaigns` | 900200 | `uuid`, `company_id`, `contact_list_id`, delivery counter columns, `launched_at`/`completed_at` |
| `omni_campaign_recipients` | 900200 | `campaign_id` + `omni_customer_id` unique, `sent_at`/`delivered_at`/`failed_at` set-once |
| `omni_voice_calls` | 900300 | `uuid`, `company_id`, `provider`, `started_at`/`ended_at` set-once, full transcript column |
| `omni_call_logs` | 900300 | Append-only, NO timestamps columns, `occurred_at` only, `company_id` enforced |
| `omni_callback_schedules` | 900300 | `company_id`, `scheduled_at`/`handled_at`, `status` enum |
| `omni_analytics` | 900400 | Unique on (company_id, agent_id, channel_type, period_date), columnar aggregates |

---

## 6. Models Created

| Model | Namespace | Table |
|-------|-----------|-------|
| `OmniAgent` | `App\Models\Omni` | `omni_agents` |
| `OmniCustomer` | `App\Models\Omni` | `omni_customers` |
| `OmniConversation` | `App\Models\Omni` | `omni_conversations` |
| `OmniMessage` | `App\Models\Omni` | `omni_messages` |
| `OmniChannelBridge` | `App\Models\Omni` | `omni_channel_bridges` |
| `OmniKnowledgeArticle` | `App\Models\Omni` | `omni_knowledge_articles` |
| `OmniAnalytics` | `App\Models\Omni` | `omni_analytics` |
| `OmniCampaign` | `App\Models\Omni\Campaign` | `omni_campaigns` |
| `OmniCampaignRecipient` | `App\Models\Omni\Campaign` | `omni_campaign_recipients` |
| `OmniContactList` | `App\Models\Omni\Campaign` | `omni_contact_lists` |
| `OmniVoiceCall` | `App\Models\Omni\Voice` | `omni_voice_calls` |
| `OmniCallLog` | `App\Models\Omni\Voice` | `omni_call_logs` |
| `OmniCallbackSchedule` | `App\Models\Omni\Voice` | `omni_callback_schedules` |

All models use `BelongsToCompany` trait.  
`OmniMessage` and `OmniCallLog` have `$timestamps = false` (immutable / append-only).

---

## 7. Events Created

All 11 events registered in `EventServiceProvider` under `MODULE: Omni` block:

| Event | Trigger |
|-------|---------|
| `OmniConversationStarted` | New conversation opened |
| `OmniMessageReceived` | Customer message ingested |
| `OmniMessageSent` | Agent/AI reply dispatched |
| `OmniConversationResolved` | Conversation closed |
| `OmniConversationTransferred` | Reassigned to different agent |
| `OmniCampaignLaunched` | Campaign broadcast started |
| `OmniCampaignCompleted` | All recipients processed |
| `OmniChannelRegistered` | New channel bridge added |
| `OmniChannelDeregistered` | Channel bridge removed |
| `OmniVoiceCallStarted` | Inbound/outbound voice call opened |
| `OmniVoiceCallEnded` | Voice call completed |

---

## 8. Config Created

`config/titan_omni.php` — registered in `TitanCoreServiceProvider::register()` via `mergeConfigFrom`.

Covers: enabled flag, default_channel, voice_provider, whatsapp_driver, telegram_token, bland_api_key, vapi_api_key, Twilio credentials, Meta credentials, analytics retention, queue names.

---

## 9. Immutability Contract Enforcement Summary

Per `TITAN_OMNI_OWNERSHIP_FREEZE.md` Section 6:

| Table / Column | Enforcement |
|---------------|-------------|
| `omni_messages` — entire row | `$timestamps = false` on model; no soft-deletes; no `updated_at` column |
| `omni_messages.delivered_at` | Set-once via app layer; no migration-level constraint needed |
| `omni_messages.read_at` | Set-once via app layer |
| `omni_campaign_recipients.sent_at` | Set-once via app layer |
| `omni_campaign_recipients.delivered_at` | Set-once via app layer |
| `omni_campaign_recipients.failed_at` | Set-once via app layer |
| `omni_voice_calls.started_at` | Set-once via app layer |
| `omni_voice_calls.ended_at` | Set-once via app layer |
| `omni_call_logs` — entire table | `$timestamps = false` on model; no `updated_at` column |

---

## 10. What Remains for Pass 03

| Item | Pass |
|------|------|
| `OmniManager::persistConversation()` + `persistMessage()` extension | Pass 03 |
| `OmniConversationService` | Pass 03 |
| `OmniChannelService` | Pass 03 |
| `OmniKnowledgeService` | Pass 03 |
| `OmniInboxService` | Pass 03 |
| `OmniAnalyticsService` | Pass 03 |
| Voice services (BlandAiService, VapiService, TwilioVoiceService, VoiceCallOrchestrator) | Pass 07 |
| Register Omni singletons in TitanCoreServiceProvider | Pass 03 |
| `routes/core/omni.routes.php` | Pass 04 |
| `routes/core/omni_webhooks.routes.php` | Pass 05 |
| Webhook controllers | Pass 05 |
| Dashboard controllers (OmniInboxController, etc.) | Pass 04 |
| Blade views | Pass 04 |
| KB unification (chatbot → omni_knowledge_articles dual-write) | Pass 08 |
| Analytics aggregation job (SyncOmniAnalytics) | Pass 09 |
| omni_audit_logs table (bridge to tz_audit_log) | Pass 09 |
