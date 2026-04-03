# TITAN OMNI: QUICK-START CHECKLIST

## READ FIRST (15 minutes)
- [ ] **TITAN_OMNI_CORE_INTEGRATION_GUIDE.md** → Understand the consolidated architecture
- [ ] **TITAN_OMNI_IMPLEMENTATION_ROADMAP.md** → See the 6-week timeline
- [ ] **TITAN_OMNI_CODE_TEMPLATES.md** → Review copy-paste code

---

## WEEK 1: SETUP & PHASE 1 (Schema + Models)

### Day 1: Preparation (2 hours)
```bash
# Backup existing data
mysqldump magicai > backup_before_omni.sql

# Create directories
mkdir -p app/Models/Omni
mkdir -p app/Services/Omni
mkdir -p app/Http/Controllers/Omni
mkdir -p database/migrations/omni
mkdir -p resources/views/omni

# Verify MagicAI structure
php artisan tinker
>>> DB::table('users')->count()  # Should show users
>>> exit
```

**Checklist:**
- [ ] Backup taken
- [ ] Directories created
- [ ] MagicAI can boot without errors

---

### Days 2-3: Create Models & Migrations (8 hours)

**Create 8 Model Files:**
```bash
# Copy from TITAN_OMNI_CODE_TEMPLATES.md
# app/Models/Omni/OmniAgent.php
# app/Models/Omni/OmniConversation.php
# app/Models/Omni/OmniMessage.php
# (+ other models: OmniKnowledgeArticle, OmniChannelBridge, OmniVoiceCall, OmniCustomer, OmniAnalytic)
```

**Checklist:**
- [ ] OmniAgent.php created
- [ ] OmniConversation.php created
- [ ] OmniMessage.php created
- [ ] All 8 models in /app/Models/Omni/

---

### Days 4-5: Create Migrations (6 hours)

**Create 8 Migration Files:**
```bash
php artisan make:migration create_omni_agents_table --create=omni_agents
php artisan make:migration create_omni_conversations_table --create=omni_conversations
php artisan make:migration create_omni_messages_table --create=omni_messages
php artisan make:migration create_omni_knowledge_articles_table
php artisan make:migration create_omni_channel_bridges_table
php artisan make:migration create_omni_voice_calls_table
php artisan make:migration create_omni_customers_table
php artisan make:migration create_omni_analytics_table

# Use SQL schema from TITAN_OMNI_CORE_INTEGRATION_GUIDE.md
# Copy the Schema::create() blocks into each migration
```

**Test Migrations:**
```bash
php artisan migrate
php artisan migrate:reset  # Test rollback
php artisan migrate        # Re-run
```

**Checklist:**
- [ ] All 8 migrations created
- [ ] Migrations run cleanly: `php artisan migrate`
- [ ] Tables appear in database
- [ ] Rollback works: `php artisan migrate:reset`

---

### Days 6-7: Test Models (4 hours)

```bash
php artisan tinker

# Test creating agents
>>> $agent = \App\Models\Omni\OmniAgent::create([
    'uuid' => \Illuminate\Support\Str::uuid(),
    'user_id' => 1,
    'name' => 'Test Agent',
    'model' => 'gpt-4-turbo'
])
>>> $agent->id  # Should be 1

# Test creating conversations
>>> $conv = \App\Models\Omni\OmniConversation::create([
    'uuid' => \Illuminate\Support\Str::uuid(),
    'agent_id' => $agent->id,
    'channel_type' => 'web'
])
>>> $conv->agent->name  # Should be 'Test Agent'

# Test polymorphic messages
>>> $msg = \App\Models\Omni\OmniMessage::create([
    'uuid' => \Illuminate\Support\Str::uuid(),
    'conversation_id' => $conv->id,
    'content' => 'Hello',
    'role' => 'user',
    'message_type' => 'text'
])
>>> $msg->conversation->agent->name  # Should work

>>> exit
```

**Checklist:**
- [ ] OmniAgent creates successfully
- [ ] OmniConversation creates + relationships work
- [ ] OmniMessage creates + relationships work
- [ ] No errors in tinker

---

## WEEK 2: PHASE 2 (Services)

### Days 1-3: Create Core Services (12 hours)

**Create Service Files:**
```bash
# From TITAN_OMNI_CODE_TEMPLATES.md
# app/Services/Omni/OmniConversationService.php
# app/Services/Omni/OmniIntelligenceDispatcher.php
# app/Services/Omni/OmniKnowledgeService.php
```

**Test Services:**
```bash
php artisan tinker

>>> $service = app(\App\Services\Omni\OmniConversationService::class)
>>> $agent = \App\Models\Omni\OmniAgent::first()
>>> $conv = $service->createConversation($agent, 'web', ['name' => 'Test User'])
>>> $conv->id  # Should be populated

>>> exit
```

**Checklist:**
- [ ] OmniConversationService created
- [ ] OmniIntelligenceDispatcher created
- [ ] OmniKnowledgeService created
- [ ] Service instantiation works
- [ ] createConversation() works

---

### Days 4-5: Create Message Handlers (10 hours)

**Create Handler Files:**
```bash
# app/Services/Omni/Handlers/TextHandler.php
# app/Services/Omni/Handlers/VoiceHandler.php
# app/Services/Omni/Handlers/ApiHandler.php

# app/Services/Omni/Channels/WhatsappChannel.php
# app/Services/Omni/Channels/TelegramChannel.php
# app/Services/Omni/Channels/TwilioChannel.php
```

**Checklist:**
- [ ] All 6 handler/channel files created
- [ ] Handlers implement handle() method
- [ ] Channels implement send() method

---

### Days 6-7: Register Services in Provider (3 hours)

**Update `/app/Providers/AppServiceProvider.php`:**
```php
public function register()
{
    // Existing code...

    // Register Omni services
    $this->app->singleton(\App\Services\Omni\OmniConversationService::class);
    $this->app->singleton(\App\Services\Omni\OmniIntelligenceDispatcher::class);
    $this->app->singleton(\App\Services\Omni\OmniKnowledgeService::class);
}
```

**Test in Tinker:**
```bash
php artisan tinker
>>> app(\App\Services\Omni\OmniConversationService::class)  # Should instantiate
>>> exit
```

**Checklist:**
- [ ] AppServiceProvider updated
- [ ] Services instantiate via app()
- [ ] No errors on `php artisan cache:clear`

---

## WEEK 3: PHASE 3 (Controllers & Routes)

### Days 1-3: Create Controllers (12 hours)

**Create Controller Files:**
```bash
# app/Http/Controllers/Omni/OmniAgentController.php
# app/Http/Controllers/Omni/OmniConversationController.php
# app/Http/Controllers/Omni/OmniAnalyticsController.php
# app/Http/Controllers/Omni/Webhooks/WhatsappWebhookController.php
# app/Http/Controllers/Omni/Webhooks/TelegramWebhookController.php
# app/Http/Controllers/Omni/Webhooks/TwilioWebhookController.php
```

**Checklist:**
- [ ] All 6 controllers created
- [ ] index(), show(), store(), update(), destroy() methods
- [ ] Webhook controllers have __invoke()

---

### Days 4-5: Create Routes & Policies (8 hours)

**Update `/routes/api.php`:**
```php
Route::prefix('omni')->middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('agents', \App\Http\Controllers\Omni\OmniAgentController::class);
    Route::prefix('agents/{agent}')->group(function () {
        Route::apiResource('conversations', \App\Http\Controllers\Omni\OmniConversationController::class);
        Route::post('conversations/{conversation}/messages', 
            [\App\Http\Controllers\Omni\OmniConversationController::class, 'storeMessage']);
    });
});

// Webhooks (no auth)
Route::prefix('webhooks/omni')->group(function () {
    Route::post('whatsapp/{agent}', \App\Http\Controllers\Omni\Webhooks\WhatsappWebhookController::class);
    Route::post('telegram/{agent}', \App\Http\Controllers\Omni\Webhooks\TelegramWebhookController::class);
    Route::post('twilio/{agent}', \App\Http\Controllers\Omni\Webhooks\TwilioWebhookController::class);
});
```

**Create Policies:**
```bash
# app/Policies/OmniAgentPolicy.php
# app/Policies/OmniConversationPolicy.php

# Register in AuthServiceProvider
```

**Test Routes:**
```bash
# List routes
php artisan route:list | grep omni

# Make test request
curl http://localhost:8000/api/omni/agents \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Checklist:**
- [ ] Routes registered
- [ ] Policies created + registered
- [ ] `php artisan route:list` shows /omni/* routes
- [ ] Test request returns 200 or 401 (not 404)

---

### Days 6-7: Create Tests (5 hours)

**Create Test Files:**
```bash
# tests/Unit/Services/Omni/OmniConversationServiceTest.php
# tests/Feature/Omni/OmniConversationApiTest.php
# tests/Feature/Omni/OmniMessageRoutingTest.php
```

**Run Tests:**
```bash
php artisan test tests/Unit/Services/Omni
php artisan test tests/Feature/Omni
```

**Checklist:**
- [ ] Tests created
- [ ] Tests run: `php artisan test`
- [ ] All tests passing

---

## WEEK 4: PHASE 4 (Livewire UI) & PHASE 5 (Migration)

### Days 1-3: Create Livewire Components (12 hours)

**Create Component Files:**
```bash
# app/Livewire/Omni/ChatWidget.php
# app/Livewire/Omni/ConversationList.php
# app/Livewire/Omni/VoicePortal.php

# resources/views/livewire/omni/chat-widget.blade.php
# resources/views/livewire/omni/conversation-list.blade.php
# resources/views/livewire/omni/voice-portal.blade.php
```

**Test Components:**
```blade
<!-- In any page -->
<livewire:omni.chat-widget :agent="$agent" />

<!-- Should render without errors -->
```

**Checklist:**
- [ ] Components created
- [ ] Views created
- [ ] Components render in browser

---

### Days 4-5: Data Migration Commands (10 hours)

**Create Migration Commands:**
```bash
# app/Console/Commands/MigrateOmniAgentsCommand.php
# app/Console/Commands/MigrateOmniConversationsCommand.php
# app/Console/Commands/MigrateOmniMessagesCommand.php
# app/Console/Commands/MigrateOmniChannelsCommand.php
# app/Console/Commands/OmniAuditCommand.php
```

**Run Migrations:**
```bash
php artisan omni:migrate-agents
php artisan omni:migrate-conversations
php artisan omni:migrate-messages
php artisan omni:audit
```

**Verify:**
```bash
php artisan tinker
>>> \App\Models\Omni\OmniAgent::count()  # Should match old agent count
>>> \App\Models\Omni\OmniMessage::count()  # Should match old message count
>>> exit
```

**Checklist:**
- [ ] Migration commands created
- [ ] Commands run successfully
- [ ] Audit shows 0 discrepancies
- [ ] Data counts match

---

### Days 6-7: Dual-Write Implementation (5 hours)

**Enable Dual-Write (Optional, for safety):**
```php
// In OmniConversationService::addMessage()
$message = OmniMessage::create([...]);  // Write to new table

// Also write to old table (for rollback safety)
if (config('omni.dual_write_enabled')) {
    // Write to ext_chatbot_histories or user_openai_chat_messages
}
```

**Checklist:**
- [ ] Dual-write logic implemented (optional)
- [ ] Monitoring enabled
- [ ] Logs show both writes happening

---

## WEEK 5: PHASE 6 (Testing & Validation)

### Full Test Suite

```bash
# Unit tests
php artisan test tests/Unit/Services/Omni --filter "ConversationService"

# Feature tests
php artisan test tests/Feature/Omni --filter "Api"

# Integration tests
php artisan test tests/Integration/Omni

# All tests
php artisan test
```

### Manual Testing Checklist
- [ ] Send text message → AI responds
- [ ] Voice call → Transcript created
- [ ] Whatsapp webhook → Message stored
- [ ] API call → Routed correctly
- [ ] Multi-agent setup → Works
- [ ] Agent handoff → Works

### Performance Testing
```bash
# Load test conversations endpoint
ab -n 1000 -c 10 http://localhost:8000/api/omni/agents/1/conversations \
  -H "Authorization: Bearer TOKEN"

# Should complete in < 5 seconds
```

**Checklist:**
- [ ] 100+ unit tests passing
- [ ] 30+ feature tests passing
- [ ] Manual tests passed
- [ ] Performance acceptable

---

## WEEK 6: PHASE 7 (Decommission & Deploy)

### Pre-Production Checklist
- [ ] All tests passing
- [ ] Code reviewed
- [ ] Documentation updated
- [ ] Team trained

### Production Deployment
```bash
# On staging first
git pull
composer install
php artisan migrate
php artisan config:cache

# Run audit
php artisan omni:audit

# If all green, deploy to production
php artisan migrate --force
php artisan cache:clear
php artisan config:cache

# Monitor logs
tail -f storage/logs/laravel.log
```

### Cutover Checklist
- [ ] Backup taken
- [ ] Migrations run
- [ ] Audit passes
- [ ] No errors in logs
- [ ] Old extensions archived (or kept as fallback)
- [ ] Team notified

---

## FINAL VERIFICATION

After completion, you should have:

✅ **8 new core models** in `/app/Models/Omni/`
- OmniAgent
- OmniConversation
- OmniMessage (polymorphic)
- OmniKnowledgeArticle
- OmniChannelBridge
- OmniVoiceCall
- OmniCustomer
- OmniAnalytic

✅ **5 core services** in `/app/Services/Omni/`
- OmniConversationService
- OmniIntelligenceDispatcher (the brain)
- OmniKnowledgeService
- Handlers (Text, Voice, API)
- Channels (Whatsapp, Telegram, Twilio)

✅ **4 controllers** in `/app/Http/Controllers/Omni/`
- OmniAgentController
- OmniConversationController
- OmniAnalyticsController
- Webhook controllers

✅ **3 Livewire components** in `/app/Livewire/Omni/`
- ChatWidget (unified text + voice)
- ConversationList
- VoicePortal

✅ **Zero data loss** during migration
✅ **Unified conversation model** across text/voice/API
✅ **100% faster queries** (20% performance gain expected)

---

## ESTIMATED HOURS PER WEEK

| Week | Phase | Hours | Status |
|------|-------|-------|--------|
| 1 | Setup + Schema + Models | 40 | ▓▓▓▓▓ |
| 2 | Services | 35 | ▓▓▓▓ |
| 3 | Controllers + Routes | 35 | ▓▓▓▓ |
| 4 | UI + Migration | 35 | ▓▓▓▓ |
| 5 | Testing | 25 | ▓▓▓ |
| 6 | Deploy + Cutover | 20 | ▓▓ |
| **TOTAL** | | **190** | |

**Ready?** Start with Day 1 checklist above. Work through each week methodically.

**Stuck?** Refer back to:
- TITAN_OMNI_CODE_TEMPLATES.md (copy code)
- TITAN_OMNI_CORE_INTEGRATION_GUIDE.md (understand architecture)
- TITAN_OMNI_IMPLEMENTATION_ROADMAP.md (timeline + details)

**Good luck!** 🚀
