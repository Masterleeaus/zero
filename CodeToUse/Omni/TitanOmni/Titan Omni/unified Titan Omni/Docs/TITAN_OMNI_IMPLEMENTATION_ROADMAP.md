# TITAN OMNI: IMPLEMENTATION ROADMAP & TECHNICAL DETAILS

**Status:** Ready to execute  
**Timeline:** 4-6 weeks to full integration  
**Effort:** ~200-250 dev hours (solo developer)

---

## ARCHITECTURE DIAGRAM

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         MAGICAI UNIFIED CORE                            │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  ┌──────────────────────────────────────────────────────────────────┐  │
│  │              OMNI INTELLIGENCE DISPATCHER (THE BRAIN)            │  │
│  │  Routes all messages → Text Handler | Voice Handler | API       │  │
│  │  Generates responses using shared OpenAI/Claude service         │  │
│  │  Retrieves context from unified knowledge base                  │  │
│  └──────────────────────────────────────────────────────────────────┘  │
│                                                                           │
│  ┌────────────────────┐  ┌────────────────────┐  ┌────────────────────┐ │
│  │  TEXT HANDLER      │  │  VOICE HANDLER     │  │  API HANDLER       │ │
│  │  - Webhook routes  │  │  - Twilio/Vonage   │  │  - REST endpoints  │ │
│  │  - Markdown render │  │  - TTS/STT routing │  │  - OAuth clients   │ │
│  │  - Sentiment       │  │  - Call transfers  │  │  - Tool calling    │ │
│  │  - Canned responses│  │  - IVR logic       │  │  - Webhooks        │ │
│  └────────────────────┘  └────────────────────┘  └────────────────────┘ │
│                                                                           │
│  ┌──────────────────────────────────────────────────────────────────┐  │
│  │              UNIFIED DATA LAYER (Core Models)                    │  │
│  │  omni_agents → omni_conversations → omni_messages ←→ knowledge   │  │
│  │  (1 conversation, 3 possible message types)                      │  │
│  └──────────────────────────────────────────────────────────────────┘  │
│                                                                           │
│  ┌────────────┐  ┌──────────────────┐  ┌────────────┐  ┌─────────────┐ │
│  │  CHANNEL   │  │  KNOWLEDGE       │  │  CUSTOMER  │  │  ANALYTICS  │ │
│  │  BRIDGES   │  │  BASE            │  │  PROFILES  │  │  SNAPSHOTS  │ │
│  │  (webhooks)│  │  (embeddings)    │  │  (CRM)     │  │  (daily)    │ │
│  └────────────┘  └──────────────────┘  └────────────┘  └─────────────┘ │
│                                                                           │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│              DELIVERY INTERFACES (Same Brain, Different Faces)           │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  ┌─────────────────────┐  ┌─────────────────────┐  ┌──────────────────┐│
│  │  WEB CHAT           │  │  VOICE PORTAL       │  │  EXTERNAL API    ││
│  │  (Livewire Widget)  │  │  (IVR+Call history) │  │  (REST client)   ││
│  │  - Text input       │  │  - Voice calling    │  │  - JSON payloads ││
│  │  - File upload      │  │  - Transcript view  │  │  - Tool calling  ││
│  │  - Avatar display   │  │  - Recording history│  │  - Async queues  ││
│  └─────────────────────┘  └─────────────────────┘  └──────────────────┘│
│                                                                           │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────────┐  │
│  │  WHATSAPP        │  │  TELEGRAM        │  │  INTERNAL DESK       │  │
│  │  (Webhook bridge)│  │  (Webhook bridge)│  │  (Human handoff)     │  │
│  │  - Message sync  │  │  - Message sync  │  │  - Agent assignment  │  │
│  │  - Media relay   │  │  - Inline buttons│  │  - Escalation flow   │  │
│  └──────────────────┘  └──────────────────┘  └──────────────────────┘  │
│                                                                           │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## PHASE-BY-PHASE IMPLEMENTATION

### PHASE 0: Preparation (Week 1 - 2 hours)

**Checklist:**
- [ ] Backup existing `ext_chatbots*`, `user_openai_chat*` data
- [ ] Create `/app/Models/Omni/` directory
- [ ] Create `/app/Services/Omni/` directory
- [ ] Create `/app/Http/Controllers/Omni/` directory
- [ ] Create `/resources/views/omni/` directory

**Commands:**
```bash
mkdir -p app/Models/Omni
mkdir -p app/Services/Omni
mkdir -p app/Http/Controllers/Omni
mkdir -p resources/views/omni
mkdir -p database/migrations/omni
```

---

### PHASE 1: Data Layer (Week 1-2 - 30 hours)

**Step 1A: Create Migrations**

Create 8 migration files in `/database/migrations/`:
```bash
# Copy template migrations (see SQL schema above)
php artisan make:migration create_omni_agents_table --create=omni_agents
php artisan make:migration create_omni_conversations_table --create=omni_conversations
php artisan make:migration create_omni_messages_table --create=omni_messages
php artisan make:migration create_omni_knowledge_articles_table
php artisan make:migration create_omni_channel_bridges_table
php artisan make:migration create_omni_voice_calls_table
php artisan make:migration create_omni_customers_table
php artisan make:migration create_omni_analytics_table
```

**Estimated Time:** 4 hours (copy/paste the SQL schema from guide, adjust for your DB)

**Step 1B: Create Eloquent Models**

Create 8 model files in `/app/Models/Omni/`:
```
- OmniAgent.php          (core)
- OmniConversation.php   (core)
- OmniMessage.php        (core - POLYMORPHIC)
- OmniKnowledgeArticle.php
- OmniChannelBridge.php
- OmniVoiceCall.php
- OmniCustomer.php
- OmniAnalytic.php
```

**Code templates** are in the guide above.

**Estimated Time:** 8 hours (relationships, scopes, casts)

**Step 1C: Create Factories & Seeders (for testing)**

```bash
php artisan make:factory OmniAgentFactory
php artisan make:factory OmniConversationFactory
php artisan make:factory OmniMessageFactory
php artisan make:seeder OmniTestDataSeeder
```

Create test data for development.

**Estimated Time:** 4 hours

**Step 1D: Run Migrations & Verify**

```bash
php artisan migrate
php artisan db:seed --class=OmniTestDataSeeder

# Check tables exist
php artisan tinker
>>> DB::table('omni_agents')->count()  // Should be > 0
>>> DB::table('omni_conversations')->count()
>>> DB::table('omni_messages')->count()
```

**Estimated Time:** 2 hours

---

### PHASE 2: Service Layer (Week 2-3 - 60 hours)

**Step 2A: Core Services**

Create in `/app/Services/Omni/`:

```
Core Services:
├── OmniConversationService.php      (30 lines - CRUD, counters)
├── OmniIntelligenceDispatcher.php   (50 lines - router + response gen)
├── OmniKnowledgeService.php         (40 lines - embeddings retrieval)
├── OmniChannelManager.php           (45 lines - webhook handlers)
├── OmniAnalyticsService.php         (35 lines - snapshots)

Message Handlers:
├── Handlers/TextHandler.php         (60 lines - text processing)
├── Handlers/VoiceHandler.php        (70 lines - voice routing)
├── Handlers/ApiHandler.php          (50 lines - REST payloads)

Channel-Specific:
├── Channels/WhatsappChannel.php     (50 lines - meta API)
├── Channels/TelegramChannel.php     (50 lines - telegram API)
├── Channels/TwilioChannel.php       (60 lines - voice calls)
├── Channels/InternalChannel.php     (40 lines - agent handoff)
```

**Code examples** in the guide above.

**Estimated Time:** 40 hours (each service ~4-8 hours)

**Step 2B: Integration with Existing Services**

Update existing services to use OmniMessage instead of old models:

```php
// OLD: /app/Services/Ai/OpenAIService.php
// Used to accept ChatBotHistory[] and UserOpenaiChatMessage[]

// NEW: accept OmniMessage[] (unified)
public function chat(array $messages, string $systemPrompt, string $model): string
{
    $payload = [
        'messages' => collect($messages)->map(fn ($msg) => [
            'role' => $msg->role,
            'content' => $msg->content,
        ])->toArray(),
        'system' => $systemPrompt,
        'model' => $model,
    ];
    // ... call OpenAI
}
```

**Update:** `/app/Services/Ai/OpenAIService.php`, `/app/Services/Stream/StreamService.php`, etc.

**Estimated Time:** 15 hours

**Step 2C: Testing**

Write unit tests in `/tests/Unit/Services/Omni/`:
```
- OmniConversationServiceTest.php
- OmniIntelligenceDispatcherTest.php
- OmniKnowledgeServiceTest.php
```

**Estimated Time:** 5 hours

---

### PHASE 3: Controllers & Routes (Week 3 - 40 hours)

**Step 3A: Create API Controllers**

Create in `/app/Http/Controllers/Omni/`:

```
├── OmniAgentController.php           (60 lines - CRUD)
├── OmniConversationController.php    (80 lines - unified list/show/message)
├── OmniAnalyticsController.php       (50 lines - dashboard data)
├── Webhooks/
│   ├── WhatsappWebhookController.php
│   ├── TelegramWebhookController.php
│   ├── TwilioWebhookController.php
│   └── GenericWebhookController.php
```

**Estimated Time:** 25 hours

**Step 3B: Define Routes**

Update `/routes/api.php`:

```php
Route::prefix('omni')->middleware(['auth:sanctum'])->group(function () {
    // Agent management
    Route::apiResource('agents', OmniAgentController::class);
    
    // Conversation management (unified)
    Route::prefix('agents/{agent}')->group(function () {
        Route::apiResource('conversations', OmniConversationController::class);
        Route::post('conversations/{conversation}/messages', 
            [OmniConversationController::class, 'storeMessage']);
    });
    
    // Analytics
    Route::get('agents/{agent}/analytics', OmniAnalyticsController::class . '@show');
});

// Webhooks (public, no auth)
Route::prefix('webhooks/omni')->group(function () {
    Route::post('whatsapp/{agent}', WhatsappWebhookController::class);
    Route::post('telegram/{agent}', TelegramWebhookController::class);
    Route::post('twilio/{agent}', TwilioWebhookController::class);
    Route::post('generic/{agent}', GenericWebhookController::class);
});
```

**Estimated Time:** 5 hours

**Step 3C: Auth & Policies**

Create `/app/Policies/OmniAgentPolicy.php`:

```php
public function view(User $user, OmniAgent $agent): bool
{
    return $user->id === $agent->user_id || 
           $user->hasTeam($agent->team_id);
}

public function update(User $user, OmniAgent $agent): bool
{
    return $user->id === $agent->user_id;
}
```

Register in `AuthServiceProvider`:
```php
protected $policies = [
    OmniAgent::class => OmniAgentPolicy::class,
    OmniConversation::class => OmniConversationPolicy::class,
];
```

**Estimated Time:** 5 hours

**Step 3D: Feature Tests**

Write feature tests in `/tests/Feature/Omni/`:
```
- OmniConversationApiTest.php
- OmniWebhookTest.php
- OmniMessageRoutingTest.php
```

**Estimated Time:** 5 hours

---

### PHASE 4: Livewire UI Components (Week 3-4 - 45 hours)

**Step 4A: Create Livewire Components**

Create in `/app/Livewire/Omni/`:

```
├── ChatWidget.php                   (Unified text + voice)
├── ConversationList.php
├── VoicePortal.php                  (Voice calling + transcript)
├── ChannelBridgeManager.php          (Webhook setup)
├── AnalyticsDashboard.php
```

**Step 4B: Create Blade Views**

Create in `/resources/views/livewire/omni/`:

```
├── chat-widget.blade.php            (40 lines - Alpine.js message loop)
├── conversation-list.blade.php
├── voice-portal.blade.php
├── channel-bridges.blade.php
├── analytics-dashboard.blade.php
```

**Estimated Time:** 35 hours (Livewire components + Blade + Alpine JS)

**Step 4C: Styling**

Use Tailwind CSS (already in MagicAI). Add custom components:
```blade
<x-omni.message-bubble :message="$message" />
<x-omni.voice-player :call="$voiceCall" />
<x-omni.channel-badge :channel="$conversation->channel_type" />
```

**Estimated Time:** 10 hours

---

### PHASE 5: Data Migration (Week 4 - 25 hours)

**Step 5A: Create Migration Console Commands**

Create in `/app/Console/Commands/`:

```
├── MigrateOmniAgentsCommand.php          (migrate from ext_chatbots)
├── MigrateOmniConversationsCommand.php   (migrate from ext_chatbot_conversations + ext_voice_chatbots)
├── MigrateOmniMessagesCommand.php        (merge ChatbotHistory + VoiceHistory + OpenAIChatMessage)
├── MigrateOmniChannelsCommand.php        (migrate webhooks)
```

**Example:**
```php
// MigrateOmniAgentsCommand.php
public function handle()
{
    // From TitanBot
    \App\Extensions\Chatbot\System\Models\Chatbot::all()->each(function ($chatbot) {
        OmniAgent::create([
            'uuid' => Str::uuid(),
            'user_id' => $chatbot->user_id,
            'name' => $chatbot->title,
            'model' => $chatbot->ai_model,
            'instructions' => $chatbot->instructions,
            'avatar_url' => $chatbot->avatar,
            // ... other fields
        ]);
    });

    // From TitanVoice
    ExtVoiceChatbot::all()->each(function ($voiceChatbot) {
        OmniAgent::create([
            'uuid' => Str::uuid(),
            'user_id' => $voiceChatbot->user_id,
            'name' => $voiceChatbot->title,
            'model' => $voiceChatbot->ai_model,
            'metadata' => [
                'voice_id' => $voiceChatbot->voice_id,
                'from_voice_suite' => true,
            ],
        ]);
    });

    $this->info('✅ Agents migrated');
}
```

**Estimated Time:** 12 hours (4 commands × 3 hours each)

**Step 5B: Validation & Audit**

Create `/app/Console/Commands/OmniAuditCommand.php`:

```php
public function handle()
{
    $agentsCount = OmniAgent::count();
    $oldAgentsCount = Chatbot::count() + ExtVoiceChatbot::count();
    
    if ($agentsCount !== $oldAgentsCount) {
        $this->error("❌ Agent count mismatch: $agentsCount vs $oldAgentsCount");
    } else {
        $this->info("✅ All agents migrated");
    }

    $messagesCount = OmniMessage::count();
    $oldMessagesCount = ChatbotHistory::count() + 
                        UserOpenaiChatMessage::count() + 
                        ExtVoicechatbotHistory::count();
    
    if ($messagesCount !== $oldMessagesCount) {
        $this->error("❌ Message count mismatch");
    } else {
        $this->info("✅ All messages migrated");
    }
}
```

**Run:**
```bash
php artisan omni:migrate-agents
php artisan omni:migrate-conversations
php artisan omni:migrate-messages
php artisan omni:migrate-channels

php artisan omni:audit
```

**Estimated Time:** 8 hours (audit + fixes)

**Step 5C: Gradual Cutover (Dual-Write Phase)**

For 1-2 weeks, write to **both** old tables + new tables:

```php
// In OmniConversationService::addMessage()
$message = OmniMessage::create([...]);  // Write to new table

// Also write to old table for rollback safety
if (request('legacy_fallback')) {
    ChatbotHistory::create([...]);  // Write to ext_chatbot_histories
}
```

**Estimated Time:** 5 hours (add dual-write logic, monitor)

---

### PHASE 6: Testing & Validation (Week 4-5 - 30 hours)

**Step 6A: Unit Tests (10 hours)**
- Message polymorphism
- Channel routing
- Knowledge retrieval
- Analytics aggregation

**Step 6B: Feature Tests (10 hours)**
- Full conversation flow (text → response)
- Voice call routing
- Webhook processing
- Multi-agent conversation
- Handoff to human agent

**Step 6C: Integration Tests (5 hours)**
- Old data access still works
- New data matches old data counts
- No data loss during migration
- Webhook delivery + processing

**Step 6D: Manual Testing (5 hours)**
- Chat on web UI
- Voice calls
- Whatsapp webhook
- API calls

**Run Tests:**
```bash
php artisan test tests/Unit/Services/Omni
php artisan test tests/Feature/Omni
php artisan test tests/Integration/Omni
```

---

### PHASE 7: Documentation & Decommission (Week 5-6 - 20 hours)

**Step 7A: Document Changes**
- Update README
- Update API docs (Swagger)
- Create migration guide for users

**Step 7B: Decommission Extensions**

After validation period (2 weeks):
```bash
# Archive old code
git mv app/Extensions/Chatbot app/Extensions/_archived/Chatbot.bak
git mv app/Extensions/AIChatPro app/Extensions/_archived/AIChatPro.bak

# Remove old models (keep for 1 month in case)
git mv app/Models/UserOpenaiChat.php app/Models/_archived/
git mv app/Models/UserOpenaiChatMessage.php app/Models/_archived/

# Remove old tables (backup first)
php artisan omni:backup-old-tables
php artisan omni:drop-old-tables --confirm
```

**Step 7C: Deploy to Production**

```bash
# Staging
git pull
composer install
php artisan migrate
php artisan omni:audit

# If all green:
# → Deploy to prod
php artisan migrate --force
php artisan cache:clear
php artisan config:cache
```

---

## WEEK-BY-WEEK TIMELINE

| Week | Phase | Tasks | Hours | Status |
|------|-------|-------|-------|--------|
| 1 | Prep + Phase 1 (Schema) | Setup dirs, create migrations, models | 40 | |
| 2 | Phase 1+2 (Services) | Services, handlers, channel integrations | 60 | |
| 3 | Phase 2+3 (Controllers) | Controllers, routes, webhooks, policies | 50 | |
| 4 | Phase 4+5 (UI+Migration) | Livewire components, data migration | 70 | |
| 5 | Phase 6 (Testing) | Tests, manual QA, validation | 30 | |
| 6 | Phase 7 (Deploy) | Documentation, cutover, decommission | 20 | |
| **TOTAL** | | | **270** | |

**Solo developer estimate:** ~6 weeks (40 hrs/week)

---

## DELIVERY CHECKLIST

### Pre-Launch
- [ ] All migrations run without error
- [ ] All 8 models created and tested
- [ ] All 5 core services working
- [ ] All 4 controllers with tests passing
- [ ] Livewire components render without error
- [ ] Webhooks verified (test with Twilio/Postman)
- [ ] Data migration audit shows 0 discrepancies
- [ ] 100+ unit tests passing
- [ ] 30+ feature tests passing
- [ ] Load test with 1000 messages (/agent shows < 500ms)

### Launch (Staging)
- [ ] Dual-write enabled (old + new tables)
- [ ] Monitoring alerts set up
- [ ] Rollback plan documented
- [ ] Team trained on new endpoints
- [ ] QA tested all 4 interfaces (web, voice, API, webhooks)

### Launch (Production)
- [ ] Backup of old tables taken
- [ ] Cutover script tested
- [ ] Monitoring dashboard live
- [ ] Support team aware
- [ ] Gradual traffic shift (10% → 25% → 50% → 100%)

### Post-Launch
- [ ] No errors in logs for 48 hours
- [ ] User-facing metrics unchanged
- [ ] Old tables archived (keep 30 days)
- [ ] Extensions deprecated in codebase

---

## ROLLBACK PLAN

If critical issues:

```bash
# Stop accepting new requests
php artisan down

# Revert to old tables
php artisan omni:restore-old-tables

# Point routes back to old models
git revert [commit]

# Clear cache
php artisan cache:clear

# Bring back online
php artisan up
```

**Estimated rollback time:** 15 minutes (zero data loss, dual-write mode)

---

## PERFORMANCE TARGETS

After consolidation, you should see:

| Metric | Before | After |
|--------|--------|-------|
| **Conversation list load** | 2-3s (3 queries) | <500ms (1 query) |
| **Message retrieval** | 1.5s (separate histories) | <400ms (single table) |
| **Webhook latency** | 800ms (routing overhead) | <300ms (direct handler) |
| **API response (chat)** | 4-5s (OpenAI only) | 4-5s (same, but cleaner code) |
| **Voice call routing** | 2s (separate logic) | <1.5s (unified dispatcher) |
| **Dashboard queries** | 20+ queries | 4 queries (snapshots) |

---

## SUCCESS METRICS

After launch:
1. ✅ **Single conversation model used across all interfaces**
2. ✅ **3 systems consolidated into 1 codebase** (Omni)
3. ✅ **Zero duplicate logic** (text, voice, API share handlers)
4. ✅ **100% backwards compatible** (old extension endpoints still work)
5. ✅ **20% faster conversation queries**
6. ✅ **Zero data loss** during migration
7. ✅ **Team can add new channels in < 2 hours** (plug into dispatcher)

---

## QUESTIONS TO ANSWER BEFORE STARTING

1. **What if voice calls come in while doing migration?**
   → Dual-write mode catches them, processes both old + new

2. **Do we keep AIChatPro UI or merge with TitanBot UI?**
   → Merge into single ChatWidget Livewire component

3. **Do we support multi-tenant (team) conversations?**
   → Yes, `team_id` in omni_conversations allows it

4. **What about existing API clients using old endpoints?**
   → Create compatibility layer that maps old → new models

5. **Where do embeddings live?** → Vector DB (pgvector, Pinecone, or Weaviate)
   → Start with `embedding_vector` in `omni_messages` table

6. **How do we handle concurrent voice + text in same conversation?**
   → Message timestamps + type field handle ordering

---

## TOOLS & DEPENDENCIES TO ADD

```bash
composer require php-ai/laravel-ai-client  # For embeddings if not using OpenAI
composer require laravel/scout             # For full-text search (optional)
npm install @headlessui/react @heroicons/react  # For UI (if needed)
```

---

**You're ready to start Phase 0 tomorrow. Good luck!**
