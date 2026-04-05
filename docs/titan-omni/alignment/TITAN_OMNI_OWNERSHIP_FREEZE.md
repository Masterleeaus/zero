# TITAN OMNI — OWNERSHIP FREEZE
**Pass:** 01 — Foundation Scan  
**Date:** 2026-04-05  
**Author:** Copilot Agent  
**Status:** FROZEN — authoritative ownership boundaries for all subsequent Omni passes

---

## Doctrine

**Reuse → extend → refactor → repair → replace only if unavoidable.**

Omni is a **messaging orchestration and channel management** layer. It does not duplicate the CRM, Finance, Work/FSM, HRM, Inventory, or Tenancy subsystems. It **connects to them** through canonical host models via `company_id`.

Tenant boundary: **`company_id`** — all Omni tables are scoped by `company_id` using the host `BelongsToCompany` trait.

---

## 1. Full Ownership Map

### 1.1 What Omni OWNS

These entities are exclusively owned and managed by Titan Omni. No other subsystem should create parallel equivalents.

| Entity | Model | Table | Notes |
|--------|-------|-------|-------|
| AI communication agents | `OmniAgent` | `omni_agents` | Omni-configured agent definitions (model, instructions, tone, channels) |
| Unified conversation threads | `OmniConversation` | `omni_conversations` | All channel types — web, WhatsApp, Telegram, Voice, Messenger, API |
| Individual messages | `OmniMessage` | `omni_messages` | Every message in a conversation — inbound + outbound |
| Channel bridge credentials | `OmniChannelBridge` | `omni_channel_bridges` | Per-company webhook + API credential store per channel |
| Omni-side customer identity | `OmniCustomer` | `omni_customers` | Channel-specific identities; linked to CRM Customer (nullable) |
| Knowledge base articles | `OmniKnowledgeArticle` | `omni_knowledge_articles` | Company-scoped KB content for RAG; replaces isolated chatbot_data over time |
| Broadcast campaigns | `OmniCampaign` | `omni_campaigns` | Multi-channel marketing/notification campaigns |
| Campaign delivery records | `OmniCampaignRecipient` | `omni_campaign_recipients` | Per-recipient delivery evidence (sent, delivered, failed) |
| Contact lists | `OmniContactList` | `omni_contact_lists` | Named recipient groups |
| Contact list membership | `OmniContactListMember` | `omni_contact_list_members` | Contact-to-list pivot |
| Voice call records | `OmniVoiceCall` | `omni_voice_calls` | Inbound + outbound calls with provider reference |
| Call event logs | `OmniCallLog` | `omni_call_logs` | Timestamped voice call events |
| Callback schedules | `OmniCallbackSchedule` | `omni_callback_schedules` | Pending customer callback requests |
| Channel analytics | `OmniAnalytics` | `omni_analytics` | Aggregated conversation/channel metrics |
| Delivery history | (in `omni_messages` + `omni_campaign_recipients`) | — | Identity evidence of every outbound message |
| Routing history | (in `omni_conversations` + `omni_messages`) | — | Which agent/channel handled each message |

---

### 1.2 What HOST Owns (Omni Reads Only)

These entities are owned by other subsystems. Omni **reads** and **links** to them. Omni must never write to these tables directly except via the owning model's public API.

| Entity | Model | Table | Host Subsystem |
|--------|-------|-------|----------------|
| Users | `User` | `users` | Auth / core |
| Companies | `Company` | `companies` | Tenancy |
| Teams | `Team` | `teams` | Tenancy / Work |
| Customers | `Customer` | `customers` (CRM) | CRM |
| Customer contacts | `CustomerContact` | `customer_contacts` | CRM |
| Customer notes | `CustomerNote` | `customer_notes` | CRM |
| Deals | `Deal` | `crm_deals` | CRM |
| Enquiries | `Enquiry` | `crm_enquiries` | CRM |
| Service jobs | `ServiceJob` | `service_jobs` | Work / FSM |
| FSM agreements | `FieldServiceAgreement` | `field_service_agreements` | Work / FSM |
| Staff profiles | `StaffProfile` | `staff_profiles` | HRM |
| Departments | `Department` | `departments` | HRM |
| Invoices / Quotes | `Invoice`, `Quote` | Finance tables | Finance |
| Budgets | `Budget` | Money tables | Finance |
| Inventory / Parts | (Inventory models) | inventory tables | Inventory |

> **Rule:** If Omni needs to associate a conversation with a CRM Customer, it stores `crm_customer_id` on `omni_customers`. It does NOT copy customer data into Omni tables.

---

### 1.3 What Is LINKED Only

These entities are co-owned or referenced bidirectionally. Omni adds nullable FK columns or uses polymorphic relations. No ownership transfer.

| Link | From (Omni) | To (Host) | Mechanism |
|------|-------------|-----------|-----------|
| Conversation → CRM Customer | `omni_conversations.crm_customer_id` | `customers.id` | nullable FK |
| Conversation → Service Job | `omni_conversations.linked_job_id` | `service_jobs.id` | nullable FK |
| Conversation → Invoice | `omni_conversations.linked_invoice_id` | invoices table | nullable FK |
| Omni Customer → CRM Customer | `omni_customers.crm_customer_id` | `customers.id` | nullable FK |
| Voice Call → Conversation | `omni_voice_calls.conversation_id` | `omni_conversations.id` | FK |
| Campaign Recipient → Omni Customer | `omni_campaign_recipients.omni_customer_id` | `omni_customers.id` | FK |
| Agent → User | `omni_agents.user_id` | `users.id` | FK |
| Agent → Company | `omni_agents.company_id` | `companies.id` | FK (tenancy) |

---

### 1.4 What CHATBOT Extension Owns Until Migration

The existing `chatbot` and `chatbot_data*` tables remain in place and are not touched during Pass 01. During Pass 02, a `company_id` column is added. These tables will eventually be superseded by `omni_agents` + `omni_knowledge_articles` under a dual-write strategy (Pass 08).

| Current Table | Current Owner | Future Omni Target | Migration Pass |
|--------------|---------------|-------------------|---------------|
| `chatbot` | Chatbot extension | → `omni_agents` | Pass 08 |
| `chatbot_data` | Chatbot extension | → `omni_knowledge_articles` | Pass 08 |
| `chatbot_data_vectors` | Chatbot extension | → embedding layer in `omni_knowledge_articles` | Pass 08 |

> **Do NOT migrate chatbot data in Pass 02.** Only add `company_id`.

---

### 1.5 What SocialMedia Extension Owns

Social Media publishing (Facebook, Instagram, LinkedIn, TikTok, X) is NOT Omni scope.

| Entity | Owner |
|--------|-------|
| Social media posts (scheduled publishing) | `app/Extensions/SocialMedia/` |
| Social media campaigns (content calendar) | `app/Extensions/AISocialMedia/` |
| Platform OAuth tokens | `app/Extensions/SocialMedia/` |

> Omni **may** receive social media inbound messages (e.g., Facebook comment replies) in a future pass via webhook. This is not Pass 01–05 scope.

---

## 2. Channel Ownership Table

| Channel | Inbound Webhook Owner | Outbound Send Owner | Credential Store |
|---------|----------------------|--------------------|--------------------|
| WhatsApp (Twilio) | `OmniWebhooks\WhatsAppWebhookController` | `TwilioVoiceService` / WhatsApp send | `omni_channel_bridges` |
| WhatsApp (Meta Cloud API) | `OmniWebhooks\WhatsAppWebhookController` | Meta Cloud API service | `omni_channel_bridges` |
| Telegram | `OmniWebhooks\TelegramWebhookController` | Telegram Bot API | `omni_channel_bridges` |
| Voice (Twilio) | `OmniWebhooks\VoiceWebhookController` | `TwilioVoiceService` | `omni_channel_bridges` |
| Voice (VAPI) | `OmniWebhooks\VoiceWebhookController` | `VapiService` | `omni_channel_bridges` |
| Voice (Bland.ai) | `OmniWebhooks\VoiceWebhookController` | `BlandAiService` | `omni_channel_bridges` |
| Facebook Messenger | `OmniWebhooks\MessengerWebhookController` | Meta Graph API | `omni_channel_bridges` |
| Web chat (embedded) | Existing `ChatbotEmbedController` (unchanged) | `OmniManager::dispatch()` | `omni_agents` |
| Generic API | `OmniWebhooks\GenericWebhookController` | n/a (API mode) | `omni_channel_bridges` |

---

## 3. Data Sovereignty Rules

### 3.1 Audit Trail Preservation

Every `OmniMessage` record is **immutable after creation**.
- No soft-deletes on `omni_messages`
- No UPDATE allowed after `delivered_at` is set
- Delivery evidence columns (`delivered_at`, `read_at`, `failed_at`, `failure_reason`) set once and never overwritten

### 3.2 Identity Evidence

Every inbound message must record:
- `channel_type` — the channel it arrived on
- `channel_id` — the channel-specific sender identity (phone number, telegram ID, etc.)
- `external_conversation_id` — the external platform's conversation/session reference
- `company_id` — tenant isolation
- `received_at` — exact timestamp

### 3.3 Routing History

Every `OmniConversation` record must preserve:
- `assigned_to` changes — through an `OmniConversationTransferred` event + message log entry
- `resolved_at` timestamp — set once on close
- Channel switches — if a conversation moves channel, a new `OmniMessage` with `content_type=system_event` is logged

### 3.4 Delivery Receipts

`OmniCampaignRecipient` records are **append-only** for status columns:
- `sent_at` — set when dispatched
- `delivered_at` — set when webhook confirms delivery
- `failed_at` — set when delivery fails
- No row deletion allowed while a campaign is active

---

## 4. Security Boundaries

| Boundary | Rule |
|----------|------|
| Cross-company conversation read | Blocked — `BelongsToCompany` global scope on all Omni models |
| Webhook channel credentials | Stored encrypted in `omni_channel_bridges.credentials` (JSON, encrypted at rest) |
| Outbound webhook verification | All outbound calls to Meta/Twilio/Telegram signed with HMAC or token |
| Inbound webhook verification | All inbound webhooks verified via signed token before `OmniManager::dispatch()` |
| Agent inbox access | `OmniConversationPolicy` — only users belonging to the conversation's `company_id` |
| Campaign launch | `OmniCampaignPolicy::launch()` — admin or manager role only |

---

## 5. TitanCore Layer Boundaries

| Component | Layer | Relationship to Omni |
|-----------|-------|---------------------|
| `OmniManager` | TitanCore | **Omni's dispatch gateway** — all channel adapters route through here |
| `TitanAIRouter` | TitanCore | AI response generation — called by OmniManager after persistence |
| `TelemetryManager` | TitanCore | Records dispatch telemetry — Omni does not own telemetry tables |
| `TitanChatBridge` | Services | Surface bridge for AIChatPro/Canvas → OmniManager |
| Channel adapters (`app/TitanCore/Chat/Channels/`) | TitanCore | Translate webhook payloads → Titan envelopes — Omni does not modify them |
| `KnowledgeManager` | TitanCore | RAG retrieval — `OmniKnowledgeService` feeds articles into it |
| `MemoryManager` | TitanCore | Conversation memory — OmniManager injects conversation context |

---

## 6. Immutability Contract

The following tables or columns are **write-once or append-only**:

| Table/Column | Rule |
|-------------|------|
| `omni_messages` — entire row | Immutable after `id` assigned. No UPDATE, no DELETE. |
| `omni_messages.delivered_at` | Set once. Cannot be overwritten. |
| `omni_messages.read_at` | Set once. Cannot be overwritten. |
| `omni_campaign_recipients.sent_at` | Set once. |
| `omni_campaign_recipients.delivered_at` | Set once. |
| `omni_campaign_recipients.failed_at` | Set once. |
| `omni_voice_calls.started_at` | Set once. |
| `omni_voice_calls.ended_at` | Set once. |
| `omni_call_logs` — entire table | Append-only. No UPDATE, no DELETE. |

---

## 7. Pass Boundary Summary

| Pass | Scope | Touches Omni Ownership? |
|------|-------|------------------------|
| Pass 01 | Architecture freeze (this document) | Defines ownership |
| Pass 02 | Schema migrations + Models | Establishes ownership in DB |
| Pass 03 | OmniManager extension + Services | Wires ownership to persistence |
| Pass 04 | Controllers + Routes | Exposes ownership via HTTP |
| Pass 05 | Webhook controllers + channel adapters | Connects external ownership |
| Pass 06 | Campaign system | Extends campaign ownership |
| Pass 07 | Voice layer | Extends voice ownership |
| Pass 08 | KB unification (chatbot→omni) | Transfers KB ownership |
| Pass 09 | Analytics | Formalises analytics ownership |
| Pass 10 | Dual-write teardown | Retires ext_* table ownership |

---

## 8. What Passes 02–10 Must NOT Change

These boundaries are **frozen** by Pass 01 and must not be altered without explicit re-architecture:

1. `company_id` is the sole tenant boundary — never `user_id` alone
2. `OmniConversation` is the single authoritative conversation record — no duplicate conversation tables
3. `OmniMessage` rows are immutable — no soft-delete, no update after delivery
4. Host CRM `Customer` is canonical customer data — `OmniCustomer` is a channel identity bridge only
5. Host Finance owns invoices and quotes — Omni may link to them, never own them
6. Host Work/FSM owns service jobs — Omni may link to them, never own them
7. `OmniManager::dispatch()` is the single routing entry point — no channel adapter may bypass it
8. All Omni models use `BelongsToCompany` trait — no exceptions
9. `omni_channel_bridges.credentials` must be encrypted at rest — no plaintext API keys
10. `routes/core/omni.routes.php` follows the same auth+throttle pattern as all other core routes

---

## 9. Deferred Scope (Not Omni Pass 01–10)

| Feature | Reason Deferred |
|---------|----------------|
| Social media inbound message listening | Social extension owns publishing; inbound webhook parsing is a separate pass |
| SMS (non-WhatsApp) | Requires Twilio SMS driver; separate from voice/WhatsApp wiring |
| Email as Omni channel | Host already has transactional email (SMTP/Mailgun). Inbound email parsing is a separate pass |
| Live chat widget (real-time) | Requires Ably/Pusher integration; `workcore.teamchat` flag; deferred after conversation core is stable |
| Mobile push notifications | FCM/APNs — separate pass after TitanCommand/TitanGo mobile app wiring |
| AI auto-reply without human review | Requires Trust layer sign-off; deferred after inbox + policy layer established |

---

## Authoritative Contact

This document is the canonical ownership reference for all Omni-related code in the host Zero codebase.
Any agent working on Omni passes **must** re-read this document before making changes.

Do not alter ownership boundaries without creating a companion `TITAN_OMNI_OWNERSHIP_REFREEZE_<reason>.md` document.
