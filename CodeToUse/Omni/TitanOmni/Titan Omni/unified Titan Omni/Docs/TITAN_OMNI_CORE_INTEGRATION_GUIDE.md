# TITAN OMNI: CORE MODULE INTEGRATION GUIDE
## Integrating Three Systems as MagicAI Unified Modules (Not Extensions)

**Status:** Architecture scan complete  
**Date:** March 25, 2026  
**Target:** Convert TitanBot + AIChatPro + TitanVoice → Core Omni modules in MagicAI  
**Approach:** Core integration (not extension-based) for tight model unification

---

## EXECUTIVE SUMMARY

Your MagicAI codebase has:
- **Existing chatbot structure** in `/app/Models/Chatbot/` (basic model + data)
- **Services layer** in `/app/Services/` (well-organized, extensible)
- **Livewire components** for UI state management
- **Existing Voice models** in `/app/Models/Voice/`
- **Provider pattern** for service registration (`AppServiceProvider`, `ChatbotServiceProvider`)

**The Integration Strategy:**
Instead of using `Extension.php` files, migrate TitanBot/AIChatPro/TitanVoice into MagicAI as **core modules**:
1. **OmniConversation** (unified conversation model, replaces 3 separate tables)
2. **OmniMessage** (polymorphic messages: text, voice, API)
3. **OmniIntelligence** (dispatcher routing to text/voice/API handler)
4. **OmniChannel** (unified channel bridge for webhooks)
5. **OmniKnowledge** (unified embeddings + vector storage)

This gives you:
- ✅ Single source of truth for conversations
- ✅ Shared knowledge across all interfaces
- ✅ Unified routing logic
- ✅ Tight integration with existing user/team/permission system
- ✅ No extension overhead—direct database constraints

---

## PART 1: DATABASE SCHEMA CONSOLIDATION

### Current State (3 Fragmented Systems)

**TitanBot** (ext_chatbots family):
```
ext_chatbots → conversations → histories + embeddings
```

**AIChatPro** (user_openai_chat family):
```
user_openai_chats → user_openai_chat_messages (no persistent tracking)
```

**TitanVoice** (voice-specific tables):
```
ext_voice_chatbots → ext_voicechatbot_conversations → ext_voicechatbot_histories
```

### New Unified Schema (Core MagicAI Tables)

**Step 1: Migrate to Core MagicAI Table Namespace (No `ext_` prefix)**

```sql
-- CORE CONVERSATION MODEL
CREATE TABLE omni_agents (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE,
    user_id BIGINT NOT NULL,
    team_id BIGINT,
    name VARCHAR(255),
    role VARCHAR(255),  -- "assistant", "custom", "service_agent"
    model VARCHAR(100),  -- "gpt-4", "claude-opus", "elevenlabs-voice"
    avatar_url VARCHAR(500),
    position VARCHAR(50) DEFAULT 'right',  -- UI position
    instructions LONGTEXT,
    system_prompt LONGTEXT,
    tone VARCHAR(50),  -- "formal", "casual", "technical"
    language VARCHAR(10) DEFAULT 'en',
    is_active BOOLEAN DEFAULT true,
    is_favorite BOOLEAN DEFAULT false,
    metadata JSON,  -- {theme, colors, custom_fields}
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL,
    INDEX user_team (user_id, team_id)
);

-- UNIFIED CONVERSATIONS (polymorphic for text/voice/API)
CREATE TABLE omni_conversations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE,
    agent_id BIGINT NOT NULL,
    customer_id BIGINT,  -- can be user or guest
    customer_email VARCHAR(255),
    customer_name VARCHAR(255),
    session_id VARCHAR(255),
    
    -- Channel metadata
    channel_type VARCHAR(50),  -- "web", "whatsapp", "telegram", "voice_call", "api", "internal"
    channel_id VARCHAR(255),  -- whatsapp_id, telegram_id, session_id, etc.
    external_conversation_id VARCHAR(255),  -- for webhook syncing
    
    -- Conversation properties
    status VARCHAR(50) DEFAULT 'open',  -- "open", "closed", "transferred", "archived"
    assigned_agent_id BIGINT,  -- for handoff to human
    is_pinned BOOLEAN DEFAULT false,
    last_activity_at TIMESTAMP,
    
    -- Message counters
    total_messages INT DEFAULT 0,
    user_messages INT DEFAULT 0,
    ai_messages INT DEFAULT 0,
    
    -- Tags and metadata
    tags JSON,  -- ["support", "billing", "escalated"]
    metadata JSON,  -- {customer_sentiment, satisfaction_score, call_duration_seconds}
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES omni_agents(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_agent_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX agent_status (agent_id, status),
    INDEX channel (channel_type, channel_id),
    INDEX last_activity (agent_id, last_activity_at DESC),
    INDEX customer (customer_email)
);

-- UNIFIED MESSAGES (polymorphic: text/voice/file/action)
CREATE TABLE omni_messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE,
    conversation_id BIGINT NOT NULL,
    agent_id BIGINT,  -- NULL for user messages
    
    -- Message type and content
    message_type VARCHAR(50),  -- "text", "voice_transcript", "image", "file", "action", "system"
    content LONGTEXT,
    role VARCHAR(50),  -- "user", "assistant", "system", "human_agent"
    
    -- Voice-specific fields
    voice_file_url VARCHAR(500),
    voice_duration_seconds INT,
    voice_model VARCHAR(100),  -- "elevenlabs", "google", "custom"
    voice_transcript TEXT,
    voice_confidence FLOAT,
    
    -- File/image fields
    media_url VARCHAR(500),
    media_type VARCHAR(50),  -- "image/png", "application/pdf"
    media_size_bytes INT,
    
    -- Embeddings for vector search
    embedding_vector VECTOR(1536),  -- for semantic search, if DB supports (pgvector, etc)
    embedding_model VARCHAR(100),  -- "text-embedding-3-small", etc
    
    -- Metadata
    read_at TIMESTAMP NULL,
    is_internal_note BOOLEAN DEFAULT false,  -- for agent notes
    external_message_id VARCHAR(255),  -- for channel sync (whatsapp_msg_id, etc)
    metadata JSON,  -- {sentiment, entities, tool_calls}
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES omni_conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES omni_agents(id) ON DELETE SET NULL,
    INDEX conversation_role (conversation_id, role),
    INDEX created_time (created_at DESC),
    INDEX embedding (embedding_model)
);

-- KNOWLEDGE BASE (unified across all agents & interfaces)
CREATE TABLE omni_knowledge_articles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE,
    agent_id BIGINT NOT NULL,
    
    title VARCHAR(500),
    content LONGTEXT,
    source_url VARCHAR(500),
    source_type VARCHAR(50),  -- "manual", "website_crawl", "pdf_upload", "text_paste"
    
    -- Categorization
    category VARCHAR(100),
    tags JSON,  -- ["faq", "policy", "workflow"]
    is_public BOOLEAN DEFAULT false,  -- accessible to other agents?
    
    -- Vector storage
    embedding_vector VECTOR(1536),
    embedding_model VARCHAR(100),
    chunk_index INT DEFAULT 0,  -- for large docs split into chunks
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES omni_agents(id) ON DELETE CASCADE,
    INDEX agent_category (agent_id, category),
    INDEX source_type (source_type)
);

-- CHANNEL BRIDGES (unified webhook management)
CREATE TABLE omni_channel_bridges (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE,
    agent_id BIGINT NOT NULL,
    
    channel_type VARCHAR(50),  -- "whatsapp", "telegram", "facebook", "instagram", "voice_twilio"
    channel_name VARCHAR(255),
    
    -- Credentials (encrypted in app)
    credentials JSON,  -- {api_key, phone_number, business_account_id, etc}
    webhook_url VARCHAR(500),
    webhook_secret VARCHAR(255),
    
    -- Channel metadata
    is_active BOOLEAN DEFAULT true,
    webhook_verified BOOLEAN DEFAULT false,
    last_webhook_at TIMESTAMP NULL,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES omni_agents(id) ON DELETE CASCADE,
    INDEX agent_channel (agent_id, channel_type)
);

-- VOICE CALL LOGS (for voice-specific analytics)
CREATE TABLE omni_voice_calls (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE,
    conversation_id BIGINT,
    agent_id BIGINT NOT NULL,
    
    phone_number VARCHAR(20),
    call_sid VARCHAR(255),  -- Twilio call SID, etc
    call_status VARCHAR(50),  -- "initiated", "ringing", "connected", "completed", "failed"
    
    duration_seconds INT DEFAULT 0,
    started_at TIMESTAMP,
    ended_at TIMESTAMP NULL,
    
    transcript TEXT,
    recording_url VARCHAR(500),
    
    metadata JSON,  -- {sentiment_score, interruptions, hold_time, transfer_reason}
    
    created_at TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES omni_conversations(id) ON DELETE SET NULL,
    FOREIGN KEY (agent_id) REFERENCES omni_agents(id) ON DELETE CASCADE,
    INDEX agent_calls (agent_id, started_at DESC)
);

-- CUSTOMER PROFILES (unified CRM-like tracking)
CREATE TABLE omni_customers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE,
    user_id BIGINT,  -- if registered user
    
    email VARCHAR(255),
    phone VARCHAR(20),
    name VARCHAR(255),
    avatar_url VARCHAR(500),
    
    total_conversations INT DEFAULT 0,
    total_messages INT DEFAULT 0,
    last_interaction_at TIMESTAMP,
    
    -- Sentiment & engagement
    sentiment_trend VARCHAR(50),  -- "positive", "neutral", "negative"
    satisfaction_score FLOAT,
    
    metadata JSON,  -- {company, timezone, language, custom_fields}
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX email (email)
);

-- ANALYTICS SNAPSHOT (for fast dashboard queries)
CREATE TABLE omni_analytics (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    agent_id BIGINT NOT NULL,
    
    snapshot_date DATE,
    total_conversations INT,
    new_conversations INT,
    closed_conversations INT,
    avg_response_time_seconds INT,
    avg_resolution_time_seconds INT,
    
    total_messages INT,
    user_messages INT,
    ai_messages INT,
    
    sentiment_positive_count INT,
    sentiment_neutral_count INT,
    sentiment_negative_count INT,
    
    voice_calls_count INT,
    voice_avg_duration_seconds INT,
    
    created_at TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES omni_agents(id) ON DELETE CASCADE,
    INDEX agent_date (agent_id, snapshot_date)
);
```

---

## PART 2: MODEL LAYER (Eloquent Models)

### Location: `/app/Models/Omni/` (NEW FOLDER)

**File: `/app/Models/Omni/OmniAgent.php`** (replaces Chatbot.php, adds voice/api context)
```php
<?php

namespace App\Models\Omni;

use App\Models\User;
use App\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OmniAgent extends Model
{
    protected $table = 'omni_agents';

    protected $fillable = [
        'uuid', 'user_id', 'team_id', 'name', 'role', 'model', 'avatar_url',
        'position', 'instructions', 'system_prompt', 'tone', 'language',
        'is_active', 'is_favorite', 'metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_favorite' => 'boolean',
        'metadata' => 'json',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(OmniConversation::class, 'agent_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(OmniMessage::class, 'agent_id');
    }

    public function knowledgeArticles(): HasMany
    {
        return $this->hasMany(OmniKnowledgeArticle::class, 'agent_id');
    }

    public function channelBridges(): HasMany
    {
        return $this->hasMany(OmniChannelBridge::class, 'agent_id');
    }

    public function voiceCalls(): HasMany
    {
        return $this->hasMany(OmniVoiceCall::class, 'agent_id');
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(OmniAnalytic::class, 'agent_id');
    }
}
```

**File: `/app/Models/Omni/OmniConversation.php`**
```php
<?php

namespace App\Models\Omni;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OmniConversation extends Model
{
    protected $table = 'omni_conversations';

    protected $fillable = [
        'uuid', 'agent_id', 'customer_id', 'customer_email', 'customer_name',
        'session_id', 'channel_type', 'channel_id', 'external_conversation_id',
        'status', 'assigned_agent_id', 'is_pinned', 'last_activity_at',
        'total_messages', 'user_messages', 'ai_messages', 'tags', 'metadata'
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'tags' => 'json',
        'metadata' => 'json',
        'last_activity_at' => 'datetime',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(OmniAgent::class, 'agent_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(OmniMessage::class, 'conversation_id')
            ->orderBy('created_at', 'asc');
    }

    public function voiceCall(): BelongsTo
    {
        return $this->belongsTo(OmniVoiceCall::class);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    // Scopes
    public function scopeByChannel($query, $channelType)
    {
        return $query->where('channel_type', $channelType);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeUnread($query)
    {
        return $query->whereHas('messages', function ($q) {
            $q->where('role', 'user')
              ->whereNull('read_at');
        });
    }
}
```

**File: `/app/Models/Omni/OmniMessage.php`** (polymorphic)
```php
<?php

namespace App\Models\Omni;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OmniMessage extends Model
{
    protected $table = 'omni_messages';

    protected $fillable = [
        'uuid', 'conversation_id', 'agent_id', 'message_type', 'content', 'role',
        'voice_file_url', 'voice_duration_seconds', 'voice_model', 'voice_transcript',
        'voice_confidence', 'media_url', 'media_type', 'media_size_bytes',
        'embedding_vector', 'embedding_model', 'read_at', 'is_internal_note',
        'external_message_id', 'metadata'
    ];

    protected $casts = [
        'is_internal_note' => 'boolean',
        'voice_duration_seconds' => 'integer',
        'voice_confidence' => 'float',
        'media_size_bytes' => 'integer',
        'metadata' => 'json',
        'read_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(OmniConversation::class, 'conversation_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(OmniAgent::class, 'agent_id');
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('message_type', $type);
    }

    public function scopeUserMessages($query)
    {
        return $query->where('role', 'user');
    }

    public function scopeAssistantMessages($query)
    {
        return $query->where('role', 'assistant');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at')->where('role', 'user');
    }

    // Accessors
    public function isVoice(): bool
    {
        return $this->message_type === 'voice_transcript';
    }

    public function isText(): bool
    {
        return $this->message_type === 'text';
    }
}
```

(Continue with OmniKnowledgeArticle, OmniChannelBridge, OmniVoiceCall, OmniCustomer, OmniAnalytic...)

---

## PART 3: SERVICE LAYER (Business Logic)

### Location: `/app/Services/Omni/` (NEW FOLDER)

**File: `/app/Services/Omni/OmniConversationService.php`**
```php
<?php

namespace App\Services\Omni;

use App\Models\Omni\OmniConversation;
use App\Models\Omni\OmniMessage;
use App\Models\Omni\OmniAgent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OmniConversationService
{
    /**
     * Get all conversations for an agent (text + voice unified)
     */
    public function getAgentConversations(OmniAgent $agent, array $filters = []): Collection|LengthAwarePaginator
    {
        $query = $agent->conversations()
            ->with('messages', 'assignedAgent')
            ->orderBy('last_activity_at', 'desc');

        // Filter by channel if provided
        if (isset($filters['channel'])) {
            $query->where('channel_type', $filters['channel']);
        }

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Unread only
        if ($filters['unread_only'] ?? false) {
            $query->whereHas('messages', function ($q) {
                $q->where('role', 'user')->whereNull('read_at');
            });
        }

        return $filters['paginate'] ?? false
            ? $query->paginate($filters['per_page'] ?? 30)
            : $query->get();
    }

    /**
     * Create a new conversation (text OR voice, unified)
     */
    public function createConversation(
        OmniAgent $agent,
        string $channelType,
        array $customerData,
        array $metadata = []
    ): OmniConversation {
        return OmniConversation::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'agent_id' => $agent->id,
            'channel_type' => $channelType,  // 'web', 'whatsapp', 'voice_call', etc
            'customer_email' => $customerData['email'] ?? null,
            'customer_name' => $customerData['name'] ?? null,
            'session_id' => $customerData['session_id'] ?? null,
            'status' => 'open',
            'metadata' => $metadata,
        ]);
    }

    /**
     * Add message (text, voice transcript, or file)
     */
    public function addMessage(
        OmniConversation $conversation,
        string $content,
        string $role = 'user',
        string $messageType = 'text',
        array $voiceData = [],
        array $mediaData = []
    ): OmniMessage {
        $message = OmniMessage::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'conversation_id' => $conversation->id,
            'agent_id' => $role === 'assistant' ? $conversation->agent_id : null,
            'content' => $content,
            'role' => $role,
            'message_type' => $messageType,

            // Voice fields
            'voice_file_url' => $voiceData['file_url'] ?? null,
            'voice_duration_seconds' => $voiceData['duration_seconds'] ?? null,
            'voice_model' => $voiceData['model'] ?? null,
            'voice_transcript' => $voiceData['transcript'] ?? null,
            'voice_confidence' => $voiceData['confidence'] ?? null,

            // Media fields
            'media_url' => $mediaData['url'] ?? null,
            'media_type' => $mediaData['type'] ?? null,
            'media_size_bytes' => $mediaData['size_bytes'] ?? null,
        ]);

        // Update conversation counters
        $conversation->increment('total_messages');
        if ($role === 'user') {
            $conversation->increment('user_messages');
        } else {
            $conversation->increment('ai_messages');
        }

        // Update last activity
        $conversation->update(['last_activity_at' => now()]);

        return $message;
    }

    /**
     * Get unread message count by channel
     */
    public function getUnreadCounts(OmniAgent $agent): array
    {
        return [
            'total' => OmniMessage::whereHas('conversation', function ($q) use ($agent) {
                $q->where('agent_id', $agent->id);
            })->where('role', 'user')->whereNull('read_at')->count(),
            'by_channel' => OmniMessage::whereHas('conversation', function ($q) use ($agent) {
                $q->where('agent_id', $agent->id);
            })->where('role', 'user')->whereNull('read_at')
                ->selectRaw('channel_type, count(*) as count')
                ->groupBy('channel_type')
                ->pluck('count', 'channel_type')
                ->toArray(),
        ];
    }
}
```

**File: `/app/Services/Omni/OmniIntelligenceDispatcher.php`** (THE BRAIN)
```php
<?php

namespace App\Services\Omni;

use App\Models\Omni\OmniConversation;
use App\Models\Omni\OmniAgent;
use App\Services\Ai\OpenAIService;
use App\Services\Omni\Handlers\TextHandler;
use App\Services\Omni\Handlers\VoiceHandler;
use App\Services\Omni\Handlers\ApiHandler;

/**
 * Routes all incoming messages to the appropriate handler.
 * Single entry point for text, voice, API, webhooks.
 */
class OmniIntelligenceDispatcher
{
    public function __construct(
        protected TextHandler $textHandler,
        protected VoiceHandler $voiceHandler,
        protected ApiHandler $apiHandler,
        protected OmniConversationService $conversationService,
    ) {}

    /**
     * Process incoming message and route intelligently
     */
    public function dispatch(
        OmniConversation $conversation,
        array $incomingPayload
    ): array {
        $messageType = $incomingPayload['type'] ?? 'text';  // text, voice, api, webhook

        return match ($messageType) {
            'text' => $this->textHandler->handle($conversation, $incomingPayload),
            'voice' => $this->voiceHandler->handle($conversation, $incomingPayload),
            'api' => $this->apiHandler->handle($conversation, $incomingPayload),
            'webhook' => $this->handleWebhook($conversation, $incomingPayload),
            default => ['error' => 'Unknown message type']
        };
    }

    /**
     * Generate response using shared intelligence layer
     */
    public function generateResponse(
        OmniConversation $conversation,
        string $userMessage
    ): string {
        $agent = $conversation->agent;

        // Retrieve context from knowledge base
        $context = app(OmniKnowledgeService::class)->retrieveContext($agent, $userMessage);

        // Build conversation history from unified message table
        $history = $conversation->messages()
            ->limit(20)
            ->get()
            ->map(fn ($msg) => [
                'role' => $msg->role,
                'content' => $msg->content,
            ])
            ->toArray();

        // Route to AI model handler
        return app(OpenAIService::class)->chat(
            messages: $history,
            systemPrompt: $agent->system_prompt,
            model: $agent->model,
            context: $context,
        );
    }

    protected function handleWebhook(OmniConversation $conversation, array $payload): array
    {
        // Identify channel from webhook
        $channel = $conversation->channel_type;

        return match ($channel) {
            'whatsapp' => $this->textHandler->handleWhatsappWebhook($conversation, $payload),
            'telegram' => $this->textHandler->handleTelegramWebhook($conversation, $payload),
            'voice_twilio' => $this->voiceHandler->handleTwilioWebhook($conversation, $payload),
            default => ['error' => 'Unknown webhook channel']
        };
    }
}
```

---

## PART 4: CONTROLLER LAYER (HTTP Endpoints)

### Location: `/app/Http/Controllers/Omni/` (NEW FOLDER)

**File: `/app/Http/Controllers/Omni/OmniConversationController.php`**
```php
<?php

namespace App\Http\Controllers\Omni;

use App\Http\Controllers\Controller;
use App\Models\Omni\OmniAgent;
use App\Models\Omni\OmniConversation;
use App\Services\Omni\OmniConversationService;
use App\Services\Omni\OmniIntelligenceDispatcher;
use Illuminate\Http\JsonResponse;

class OmniConversationController extends Controller
{
    public function __construct(
        protected OmniConversationService $conversationService,
        protected OmniIntelligenceDispatcher $dispatcher,
    ) {}

    /**
     * List all conversations for an agent (unified view)
     * Works for: internal web UI, voice portal, API clients
     */
    public function index(OmniAgent $agent): JsonResponse
    {
        $this->authorize('view', $agent);

        $conversations = $this->conversationService->getAgentConversations($agent, [
            'paginate' => true,
            'per_page' => request('per_page', 30),
            'channel' => request('channel'),
            'status' => request('status', 'open'),
            'unread_only' => request('unread_only', false),
        ]);

        return response()->json([
            'data' => $conversations->items(),
            'pagination' => [
                'total' => $conversations->total(),
                'per_page' => $conversations->perPage(),
                'current_page' => $conversations->currentPage(),
            ],
            'unread_counts' => $this->conversationService->getUnreadCounts($agent),
        ]);
    }

    /**
     * Get single conversation with all messages
     * Works for: web chat widget, voice history portal, API
     */
    public function show(OmniAgent $agent, OmniConversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        return response()->json([
            'conversation' => $conversation->load('messages', 'agent'),
            'agent' => [
                'id' => $conversation->agent->id,
                'name' => $conversation->agent->name,
                'avatar' => $conversation->agent->avatar_url,
                'model' => $conversation->agent->model,
            ],
        ]);
    }

    /**
     * Send message (text, voice transcript, file, or API call)
     * Single endpoint for all input types
     */
    public function storeMessage(OmniAgent $agent, OmniConversation $conversation): JsonResponse
    {
        $this->authorize('update', $conversation);

        $incomingPayload = [
            'type' => request('type', 'text'),  // text, voice, api, webhook
            'content' => request('content'),
            'voice_data' => request('voice_data'),
            'media_data' => request('media_data'),
        ];

        // Dispatch to appropriate handler
        $response = $this->dispatcher->dispatch($conversation, $incomingPayload);

        // If error, return 400
        if (isset($response['error'])) {
            return response()->json($response, 400);
        }

        // Generate AI response
        $aiResponse = $this->dispatcher->generateResponse(
            $conversation,
            $incomingPayload['content']
        );

        // Store both user and AI messages
        $userMessage = $this->conversationService->addMessage(
            $conversation,
            $incomingPayload['content'],
            'user',
            $incomingPayload['type'],
            $incomingPayload['voice_data'] ?? [],
            $incomingPayload['media_data'] ?? [],
        );

        $aiMessage = $this->conversationService->addMessage(
            $conversation,
            $aiResponse,
            'assistant',
            $incomingPayload['type'] === 'voice' ? 'voice_transcript' : 'text',
        );

        return response()->json([
            'user_message' => $userMessage,
            'ai_message' => $aiMessage,
        ]);
    }
}
```

---

## PART 5: MIGRATION STRATEGY

### Step 1: Create New Core Tables
```bash
php artisan make:migration create_omni_agents_table
php artisan make:migration create_omni_conversations_table
php artisan make:migration create_omni_messages_table
php artisan make:migration create_omni_knowledge_articles_table
php artisan make:migration create_omni_channel_bridges_table
php artisan make:migration create_omni_voice_calls_table
php artisan make:migration create_omni_customers_table
php artisan make:migration create_omni_analytics_table
```

### Step 2: Data Migration Script
```bash
# Migrate data from ext_chatbots → omni_agents
php artisan migrate:omni:agents

# Migrate data from ext_chatbot_conversations → omni_conversations
php artisan migrate:omni:conversations

# Migrate data from ext_chatbot_histories + user_openai_chat_messages → omni_messages
php artisan migrate:omni:messages

# Voice data from ext_voice_chatbots → omni_agents (with voice metadata)
php artisan migrate:omni:voice-agents

# Webhooks from ext_chatbot_channels → omni_channel_bridges
php artisan migrate:omni:channels
```

### Step 3: Verification
```bash
# Validate data integrity
php artisan omni:validate-migration

# Check message counts match
php artisan omni:audit-messages

# Verify channel bridges
php artisan omni:audit-channels
```

### Step 4: Gradual Cutover (Zero Downtime)
1. **Week 1**: Run migrations, populate new tables, keep old tables untouched
2. **Week 2**: Deploy dual-write code (writes to both old + new simultaneously)
3. **Week 3**: Switch read traffic to new tables, monitor
4. **Week 4**: Archive old tables, decommission extensions

---

## PART 6: INTEGRATION POINTS (What to Update)

### Routes
**File: `/routes/panel.php` & `/routes/api.php`**

```php
// OLD (Extension-based)
// Route::prefix('chatbot')->group(base_path('app/Extensions/Chatbot/routes/web.php'));

// NEW (Core module)
Route::prefix('omni')->middleware(['auth'])->group(function () {
    Route::apiResource('agents', OmniAgentController::class);
    Route::apiResource('conversations', OmniConversationController::class);
    Route::post('conversations/{conversation}/messages', [OmniConversationController::class, 'storeMessage']);
    
    // Webhooks (no auth needed)
    Route::post('webhooks/whatsapp/{agent}', WhatsappWebhookController::class);
    Route::post('webhooks/telegram/{agent}', TelegramWebhookController::class);
    Route::post('webhooks/voice/{agent}', VoiceWebhookController::class);
});
```

### Service Provider
**File: `/app/Providers/AppServiceProvider.php`**

```php
public function register()
{
    // Register Omni services
    $this->app->singleton(OmniConversationService::class);
    $this->app->singleton(OmniIntelligenceDispatcher::class);
    $this->app->singleton(OmniKnowledgeService::class);
    $this->app->singleton(OmniChannelManager::class);
}
```

### Livewire Components
**File: `/app/Livewire/Omni/ChatWidget.php`** (replaces 3 separate components)

```php
class ChatWidget extends Component
{
    public OmniAgent $agent;
    public OmniConversation $conversation;
    public array $messages = [];

    public function mount(OmniAgent $agent)
    {
        $this->agent = $agent;
        // Create or retrieve conversation
        $this->conversation = $this->getOrCreateConversation();
        $this->loadMessages();
    }

    public function sendMessage(string $content, string $type = 'text')
    {
        // Single handler for text, voice, file
        $this->dispatcher->dispatch($this->conversation, [
            'type' => $type,
            'content' => $content,
        ]);
        $this->loadMessages();
    }

    public function render()
    {
        return view('livewire.omni.chat-widget', [
            'agent' => $this->agent,
            'messages' => $this->messages,
        ]);
    }
}
```

---

## PART 7: CONSOLIDATION CHECKLIST

### Database
- [ ] Create `omni_agents` table
- [ ] Create `omni_conversations` table
- [ ] Create `omni_messages` table (UNIFIED: text + voice + files)
- [ ] Create `omni_knowledge_articles` table
- [ ] Create `omni_channel_bridges` table
- [ ] Create `omni_voice_calls` table
- [ ] Create `omni_customers` table
- [ ] Create `omni_analytics` table

### Models
- [ ] OmniAgent (replaces Chatbot + ExtVoiceChatbot)
- [ ] OmniConversation (replaces ChatbotConversation + VoiceConversation)
- [ ] OmniMessage (replaces ChatbotHistory + VoiceHistory + OpenAIChatMessage)
- [ ] OmniKnowledgeArticle
- [ ] OmniChannelBridge
- [ ] OmniVoiceCall
- [ ] OmniCustomer
- [ ] OmniAnalytic

### Services
- [ ] OmniConversationService
- [ ] OmniIntelligenceDispatcher (the brain)
- [ ] OmniKnowledgeService (unified embeddings)
- [ ] OmniChannelManager (webhooks)
- [ ] TextHandler, VoiceHandler, ApiHandler

### Controllers
- [ ] OmniAgentController
- [ ] OmniConversationController (unified endpoint)
- [ ] OmniMessageController
- [ ] Webhook handlers (Whatsapp, Telegram, Voice, etc)

### Views/UI
- [ ] ChatWidget Livewire component (works for text + voice)
- [ ] VoicePortal component
- [ ] Analytics dashboard
- [ ] Channel bridge management

### Routes
- [ ] `/omni/agents` (CRUD)
- [ ] `/omni/conversations` (unified list)
- [ ] `/omni/conversations/{id}` (unified view)
- [ ] `/omni/conversations/{id}/messages` (POST for any type)
- [ ] `/omni/webhooks/*` (channel bridges)

### Tests
- [ ] Message polymorphism test
- [ ] Channel routing test
- [ ] Voice+text conversation interop test
- [ ] Knowledge retrieval test

---

## PART 8: FILES TO DELETE/DEPRECATE

Once migrated:
```
❌ /app/Extensions/Chatbot/                  (move to /app/Models/Omni/*)
❌ /app/Extensions/AIChatPro/                (merge into /app/Services/Omni/*)
❌ /app/Extensions/TitanVoice/               (merge into /app/Services/Omni/*)
❌ /app/Models/ChatBotHistory.php            (→ OmniMessage)
❌ /app/Models/Chatbot/Chatbot.php (old)    (→ OmniAgent)
❌ /app/Models/UserOpenaiChat.php            (→ OmniConversation)
❌ /app/Models/UserOpenaiChatMessage.php     (→ OmniMessage)
❌ /app/Services/Chatbot/                    (→ /app/Services/Omni/*)
```

---

## SUMMARY: Benefits of Core Integration (vs Extensions)

| Aspect | Extension | **Core Module** |
|--------|-----------|-----------------|
| **Database constraints** | Loose (ext_* prefix) | Tight (foreign keys) |
| **Code organization** | Isolated | Integrated |
| **Shared models** | Hard to share | Native relationships |
| **Performance** | Extra queries | Single-pass eager loading |
| **User/Team ACL** | Manual checks | Via Policies |
| **Webhook management** | Separate logic | Unified bridge |
| **Analytics** | Per-system | Unified snapshots |
| **Testing** | Mocked | Real integration |

**Result:** One unified "Titan Omni" operating system across web, voice, API, and webhooks—sharing the same agent, knowledge, customer profile, and intelligence dispatcher.
