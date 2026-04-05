# TITAN OMNI — MODEL IMPLEMENTATION REPORT
**Pass:** 03 — Models + Relationships + Tenancy  
**Date:** 2026-04-05  
**Author:** Copilot Agent  
**Status:** COMPLETE

---

## 1. What Was Scanned

### Docs Scanned
- `docs/titan-omni/alignment/TITAN_OMNI_OWNERSHIP_FREEZE.md` — ownership boundaries, tenancy rules, immutability contracts
- `docs/titan-omni/migrations/TITAN_OMNI_MIGRATION_IMPLEMENTATION_REPORT.md` — Pass 02 table list, column specs, model stubs created
- `docs/titan-omni/source-maps/TITAN_OMNI_HOST_INSERTION_MAP.md` — planned service list, singleton registration points
- `docs/titan-omni/source-maps/TITAN_OMNI_EXISTING_CODE_AUDIT.md` — existing Omni, chatbot, voice code inventory

### CodeToUse Scanned
- `CodeToUse/Voice/TitanOmni Complete Pass26 HARDENED/titan_omni_complete/app/Models/Omni/` — donor model patterns (OmniConversation, OmniMessage, OmniAgent, OmniCustomer, OmniKnowledgeArticle, OmniChannelBridge, OmniAnalytic)
- `CodeToUse/Voice/TitanOmni Complete Pass26 HARDENED/titan_omni_complete/app/Services/Omni/` — donor service patterns (OmniConversationService, OmniKnowledgeService, OmniChannelManager)
- `CodeToUse/Omni/TitanOmni/TitanHelloBase/Models/` — TitanHello donor models (Call, VoiceCall, VoiceSession, CallEvent, DialCampaign)

### Host Code Scanned
- `app/Models/Concerns/BelongsToCompany.php` — global scope + company_id resolution pattern
- `app/Models/Crm/Customer.php` — CRM Customer model (for host relationship bridges)
- `app/Models/Work/ServiceJob.php` — ServiceJob model (for conversation context links)
- `app/Models/Money/Invoice.php` — Invoice model (for billing context links)
- `app/Models/User.php` — User model (for assigned_to + user_id relationships)
- `app/Providers/TitanCoreServiceProvider.php` — singleton registration pattern
- Existing Pass 02 model stubs in `app/Models/Omni/`

---

## 2. Donor Model Code Reused

| Host File | Donor Source | What Was Reused |
|-----------|-------------|-----------------|
| `OmniConversationService` | `CodeToUse/Voice/.../OmniConversationService.php` | `findOrCreate()` idempotent match pattern, `appendMessage()` structure, error handling doctrine |
| `OmniKnowledgeService` | `CodeToUse/Voice/.../OmniKnowledgeService.php` | `search()` query with agent_id null-or-match pattern |
| `OmniAgent` relationships | Donor OmniAgent model | `conversations()`, `channelBridges()`, `knowledgeArticles()` HasMany patterns |
| `OmniMessage` immutability | Donor model design + Ownership Freeze | Set-once delivery timestamp columns, `$timestamps = false` |
| Sequence/Campaign structures | TitanHello `DialCampaign` + `CallbackRequest` | Step/run execution concepts adapted for OmniSequence |

---

## 3. Host Patterns Followed

| Pattern | Applied To |
|---------|-----------|
| `BelongsToCompany` trait on all Omni models | Every model in `app/Models/Omni/` |
| `declare(strict_types=1)` | All new PHP files |
| `protected $hidden` for credentials | `OmniChannelBridge` — hides `credentials`, `webhook_secret` |
| `$timestamps = false` for append-only tables | `OmniMessage`, `OmniCallLog`, `OmniMessageAttachment` |
| `boot()` static method for UUID auto-generation | `OmniAgent`, `OmniConversation`, `OmniCustomer`, `OmniMessage`, `OmniVoiceCall`, `OmniCampaign`, `OmniSequence`, `OmniAutomation` |
| `scopeX()` named query scopes | All models — `scopeActive`, `scopeOpen`, `scopeForChannel`, etc. |
| `singleton()` registration in `TitanCoreServiceProvider` | All 5 new Omni services |

---

## 4. Model Files Created / Extended

### 4.1 Models Extended (Pass 02 stubs → Pass 03 full models)

| Model | Path | Extension |
|-------|------|-----------|
| `OmniAgent` | `app/Models/Omni/OmniAgent.php` | Added `user()`, `sequences()`, `automations()`, `handoffRules()` relationships; `HasOmniTenancy` trait; `scopeActive`, `scopeForChannel`, `scopeWithContext` |
| `OmniConversation` | `app/Models/Omni/OmniConversation.php` | Added `crmCustomer()`, `serviceJob()`, `invoice()`, `assignedUser()` host relationships; `HasOmniTenancy` trait; `scopeForChannel`, `scopeWithInboxContext` N+1 guard |
| `OmniCustomer` | `app/Models/Omni/OmniCustomer.php` | Added `crmCustomer()` host bridge relationship; `HasOmniTenancy` trait; `scopeOnChannel`, `scopeWithCrmContext` |
| `OmniMessage` | `app/Models/Omni/OmniMessage.php` | Added `attachments()` HasMany; `HasImmutableTimestamps` trait with `$immutableColumns = ['delivered_at', 'read_at', 'failed_at']`; `scopeInbound`, `scopeOutbound`, `scopeUndelivered`, `scopeWithAttachments` |
| `OmniCallLog` | `app/Models/Omni/Voice/OmniCallLog.php` | Added `HasImmutableTimestamps` trait; `scopeOfType` |

### 4.2 New Models Created

| Model | Path | Table |
|-------|------|-------|
| `OmniMessageAttachment` | `app/Models/Omni/OmniMessageAttachment.php` | `omni_message_attachments` |
| `OmniContactListMember` | `app/Models/Omni/Campaign/OmniContactListMember.php` | `omni_contact_list_members` (pivot as model) |
| `OmniSequence` | `app/Models/Omni/Automation/OmniSequence.php` | `omni_sequences` |
| `OmniSequenceStep` | `app/Models/Omni/Automation/OmniSequenceStep.php` | `omni_sequence_steps` |
| `OmniSequenceRun` | `app/Models/Omni/Automation/OmniSequenceRun.php` | `omni_sequence_runs` |
| `OmniAutomation` | `app/Models/Omni/Automation/OmniAutomation.php` | `omni_automations` |
| `OmniAutomationAction` | `app/Models/Omni/Automation/OmniAutomationAction.php` | `omni_automation_actions` |
| `OmniOverlayBinding` | `app/Models/Omni/Automation/OmniOverlayBinding.php` | `omni_overlay_bindings` |
| `OmniHandoffRule` | `app/Models/Omni/Automation/OmniHandoffRule.php` | `omni_handoff_rules` |

---

## 5. Traits Created

| Trait | Path | Purpose |
|-------|------|---------|
| `HasImmutableTimestamps` | `app/Models/Traits/HasImmutableTimestamps.php` | Prevents write-once columns from being overwritten; `scopeRecentDays` helper |
| `HasOmniTenancy` | `app/Models/Traits/HasOmniTenancy.php` | Omni-specific tenant-scoping helpers on top of `BelongsToCompany`; `scopeForOmniCompany`, `scopeWithActiveAgent`, `scopeWithConversationContext`, `scopeCreatedWithin` |

---

## 6. Services Created

| Service | Path | Key Methods |
|---------|------|-------------|
| `OmniConversationService` | `app/Services/Omni/OmniConversationService.php` | `findOrCreate()`, `appendMessage()`, `resolve()`, `transfer()` |
| `OmniChannelService` | `app/Services/Omni/OmniChannelService.php` | `register()`, `deregister()`, `markVerified()`, `activeBridges()` |
| `OmniKnowledgeService` | `app/Services/Omni/OmniKnowledgeService.php` | `search()`, `upsert()`, `listActive()`, `archive()` |
| `OmniInboxService` | `app/Services/Omni/OmniInboxService.php` | `paginatedInbox()`, `assign()`, `claim()`, `messageHistory()`, `openCountsPerAgent()` |
| `OmniAnalyticsService` | `app/Services/Omni/OmniAnalyticsService.php` | `increment()`, `periodReport()`, `summary()` |

All 5 services registered as singletons in `TitanCoreServiceProvider::register()`.

---

## 7. OmniManager Extended

`app/TitanCore/Omni/OmniManager.php` extended with:

| Method | Signature | Purpose |
|--------|-----------|---------|
| `persistConversation` | `(array $envelope): ?OmniConversation` | Delegates to `OmniConversationService::findOrCreate()` using the normalised envelope |
| `persistMessage` | `(OmniConversation $conv, array $envelope): ?OmniMessage` | Delegates to `OmniConversationService::appendMessage()` |

The `OmniConversationService` is an optional constructor parameter — graceful null return when not injected (backward-compatible with workspace chat).

---

## 8. Migration Created

| File | Tables | Number |
|------|--------|--------|
| `2026_04_05_900500_create_omni_automation_tables.php` | `omni_sequences`, `omni_sequence_steps`, `omni_sequence_runs`, `omni_automations`, `omni_automation_actions`, `omni_overlay_bindings`, `omni_handoff_rules`, `omni_message_attachments` | 900500 |

**Total new tables in Pass 03:** 8  
**Cumulative Omni tables:** 22

---

## 9. Relationship Pattern Summary

All relationships follow N+1 safety rules:

| From | Relationship | To | N+1 Guard Scope |
|------|-------------|-----|----------------|
| `OmniAgent` | `hasMany` | `OmniConversation` | — |
| `OmniAgent` | `hasMany` | `OmniChannelBridge` | `scopeWithContext()` eager-loads bridges |
| `OmniAgent` | `hasMany` | `OmniKnowledgeArticle` | — |
| `OmniAgent` | `hasMany` | `OmniSequence` | — |
| `OmniAgent` | `hasMany` | `OmniAutomation` | — |
| `OmniAgent` | `hasMany` | `OmniHandoffRule` | — |
| `OmniAgent` | `belongsTo` | `User` (host) | — |
| `OmniConversation` | `belongsTo` | `OmniAgent` | `scopeWithInboxContext()` eager-loads agent |
| `OmniConversation` | `belongsTo` | `OmniCustomer` | `scopeWithInboxContext()` eager-loads customer |
| `OmniConversation` | `belongsTo` | `Customer` (CRM) | Read-only host bridge |
| `OmniConversation` | `belongsTo` | `ServiceJob` (Work) | Read-only host bridge |
| `OmniConversation` | `belongsTo` | `Invoice` (Finance) | Read-only host bridge |
| `OmniConversation` | `belongsTo` | `User` (assigned_to) | `scopeWithInboxContext()` eager-loads user |
| `OmniConversation` | `hasMany` | `OmniMessage` | — |
| `OmniConversation` | `hasMany` | `OmniVoiceCall` | — |
| `OmniCustomer` | `belongsTo` | `Customer` (CRM) | `scopeWithCrmContext()` eager-loads CRM |
| `OmniCustomer` | `hasMany` | `OmniConversation` | — |
| `OmniCustomer` | `hasMany` | `OmniVoiceCall` | — |
| `OmniMessage` | `belongsTo` | `OmniConversation` | — |
| `OmniMessage` | `belongsTo` | `OmniAgent` | — |
| `OmniMessage` | `hasMany` | `OmniMessageAttachment` | `scopeWithAttachments()` eager-loads attachments |
| `OmniSequence` | `hasMany` | `OmniSequenceStep` | `scopeWithSteps()` |
| `OmniSequence` | `hasMany` | `OmniSequenceRun` | — |
| `OmniSequenceRun` | `belongsTo` | `OmniSequence` | `scopeWithContext()` |
| `OmniSequenceRun` | `belongsTo` | `OmniCustomer` | `scopeWithContext()` |
| `OmniSequenceRun` | `belongsTo` | `OmniSequenceStep` (current) | `scopeWithContext()` |
| `OmniAutomation` | `hasMany` | `OmniAutomationAction` | `scopeWithActions()` |
| `OmniHandoffRule` | `belongsTo` | `OmniAgent` | — |
| `OmniHandoffRule` | `belongsTo` | `User` (target) | `scopeWithContext()` |
| `OmniOverlayBinding` | `belongsTo` | `OmniAgent` | — |
| `OmniOverlayBinding` | `belongsTo` | `OmniConversation` | — |
| `OmniContactList` | `belongsToMany` | `OmniCustomer` (via pivot) | — |
| `OmniCampaign` | `hasMany` | `OmniCampaignRecipient` | — |
| `OmniVoiceCall` | `hasMany` | `OmniCallLog` | — |

---

## 10. Tenancy Summary

Every Omni model uses the `BelongsToCompany` trait which:
- Registers a global `company` scope using `Auth::user()->company_id` (request-cached)
- Auto-sets `company_id` on new records when authenticated
- Provides `scopeForCompany(int $companyId)` for admin bypass

Additionally, models using `HasOmniTenancy` gain:
- `scopeForOmniCompany(int $companyId)` — cross-company admin bypass
- `scopeWithConversationContext()` — N+1-safe eager load
- `scopeCreatedWithin(int $days)` — temporal filter

---

## 11. Test Coverage

`tests/Feature/Omni/OmniPass03Test.php` — 20 test cases covering:

| Test Area | Count |
|-----------|-------|
| Model class existence | 2 |
| Tenancy / global scope isolation | 2 |
| Relationship declarations | 4 |
| OmniConversationService (findOrCreate, appendMessage, resolve) | 4 |
| OmniChannelService (register, deregister) | 2 |
| OmniInboxService (paginated inbox, assign) | 2 |
| OmniAnalyticsService (increment, summary) | 2 |
| OmniManager extensions | 2 |
| New model CRUD (Sequence, Automation, HandoffRule, Overlay, Attachment) | 5 |
| HasImmutableTimestamps immutable column list | 1 |

---

## 12. What Was NOT Changed (Ownership Frozen)

Per `TITAN_OMNI_OWNERSHIP_FREEZE.md` Section 8:

- `customers`, `customer_contacts` tables — untouched (CRM owns)
- `service_jobs` table — untouched (Work/FSM owns)
- Finance/invoice tables — untouched (Finance owns)
- `chatbot`, `chatbot_data`, `chatbot_data_vectors` — untouched (chatbot extension owns until Pass 08)
- `OmniManager::dispatch()` signature — unchanged (backward compatible; new methods are additive)

---

## 13. What Remains for Pass 04

| Item | Pass |
|------|------|
| `routes/core/omni.routes.php` — dashboard routes | Pass 04 |
| `OmniInboxController`, `OmniConversationController` | Pass 04 |
| `OmniAgentController`, `OmniChannelController` | Pass 04 |
| `OmniCampaignController`, `OmniKnowledgeController` | Pass 04 |
| Blade views (inbox, conversation, agents, channels, campaigns, KB) | Pass 04 |
| `routes/core/omni_webhooks.routes.php` | Pass 05 |
| Webhook controllers (WhatsApp, Telegram, Voice, Messenger, Generic) | Pass 05 |
| `OmniCampaignService` (campaign launch + delivery tracking) | Pass 06 |
| Voice services (BlandAiService, VapiService, TwilioVoiceService, VoiceCallOrchestrator) | Pass 07 |
| KB dual-write (chatbot → omni_knowledge_articles) | Pass 08 |
| `SyncOmniAnalytics` scheduled job | Pass 09 |
| Audit log bridge to `tz_audit_log` | Pass 09 |
