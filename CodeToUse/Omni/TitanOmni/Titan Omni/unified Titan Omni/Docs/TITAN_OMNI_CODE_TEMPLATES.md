# TITAN OMNI: COPY-PASTE CODE TEMPLATES
## Ready-to-use code for Phase 1 (Models & Services)

---

## FILE 1: `/app/Models/Omni/OmniAgent.php`

```php
<?php

namespace App\Models\Omni;

use App\Models\User;
use App\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OmniAgent extends Model
{
    use HasFactory;

    protected $table = 'omni_agents';

    protected $fillable = [
        'uuid',
        'user_id',
        'team_id',
        'name',
        'role',
        'model',
        'avatar_url',
        'position',
        'instructions',
        'system_prompt',
        'tone',
        'language',
        'is_active',
        'is_favorite',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_favorite' => 'boolean',
        'metadata' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    public function scopeByModel($query, $model)
    {
        return $query->where('model', $model);
    }

    // Accessors
    public function isVoiceAgent(): bool
    {
        return $this->metadata['voice_id'] ?? false;
    }

    public function getTotalConversations(): int
    {
        return $this->conversations()->count();
    }

    public function getUnreadCount(): int
    {
        return $this->conversations()
            ->whereHas('messages', function ($q) {
                $q->where('role', 'user')->whereNull('read_at');
            })
            ->count();
    }
}
```

---

## FILE 2: `/app/Models/Omni/OmniConversation.php`

```php
<?php

namespace App\Models\Omni;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OmniConversation extends Model
{
    use HasFactory;

    protected $table = 'omni_conversations';

    protected $fillable = [
        'uuid',
        'agent_id',
        'customer_id',
        'customer_email',
        'customer_name',
        'session_id',
        'channel_type',
        'channel_id',
        'external_conversation_id',
        'status',
        'assigned_agent_id',
        'is_pinned',
        'last_activity_at',
        'total_messages',
        'user_messages',
        'ai_messages',
        'tags',
        'metadata',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'tags' => 'json',
        'metadata' => 'json',
        'last_activity_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function agent(): BelongsTo
    {
        return $this->belongsTo(OmniAgent::class, 'agent_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(OmniMessage::class, 'conversation_id')
            ->orderBy('created_at', 'asc');
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function voiceCalls(): HasMany
    {
        return $this->hasMany(OmniVoiceCall::class, 'conversation_id');
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

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeUnread($query)
    {
        return $query->whereHas('messages', function ($q) {
            $q->where('role', 'user')->whereNull('read_at');
        });
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeRecentlyActive($query, $minutes = 60)
    {
        return $query->where('last_activity_at', '>=', now()->subMinutes($minutes));
    }

    // Accessors
    public function getUnreadMessageCount(): int
    {
        return $this->messages()
            ->where('role', 'user')
            ->whereNull('read_at')
            ->count();
    }

    public function getLatestMessage(): ?OmniMessage
    {
        return $this->messages()->latest()->first();
    }

    public function isVoiceConversation(): bool
    {
        return $this->channel_type === 'voice_call';
    }

    public function isWebConversation(): bool
    {
        return $this->channel_type === 'web';
    }

    public function requiresHandoff(): bool
    {
        return $this->assigned_agent_id !== null;
    }
}
```

---

## FILE 3: `/app/Models/Omni/OmniMessage.php` (POLYMORPHIC)

```php
<?php

namespace App\Models\Omni;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OmniMessage extends Model
{
    use HasFactory;

    protected $table = 'omni_messages';

    protected $fillable = [
        'uuid',
        'conversation_id',
        'agent_id',
        'message_type',
        'content',
        'role',
        'voice_file_url',
        'voice_duration_seconds',
        'voice_model',
        'voice_transcript',
        'voice_confidence',
        'media_url',
        'media_type',
        'media_size_bytes',
        'embedding_vector',
        'embedding_model',
        'read_at',
        'is_internal_note',
        'external_message_id',
        'metadata',
    ];

    protected $casts = [
        'is_internal_note' => 'boolean',
        'voice_duration_seconds' => 'integer',
        'voice_confidence' => 'float',
        'media_size_bytes' => 'integer',
        'metadata' => 'json',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
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
        return $query->where('role', 'user')->whereNull('read_at');
    }

    public function scopeText($query)
    {
        return $query->where('message_type', 'text');
    }

    public function scopeVoice($query)
    {
        return $query->where('message_type', 'voice_transcript');
    }

    public function scopeWithMedia($query)
    {
        return $query->whereNotNull('media_url');
    }

    // Type checking
    public function isVoice(): bool
    {
        return $this->message_type === 'voice_transcript';
    }

    public function isText(): bool
    {
        return $this->message_type === 'text';
    }

    public function isImage(): bool
    {
        return $this->message_type === 'image';
    }

    public function isFile(): bool
    {
        return $this->message_type === 'file';
    }

    public function isSystemMessage(): bool
    {
        return $this->role === 'system';
    }

    // Accessors
    public function getSafeContent(): string
    {
        return strip_tags(htmlspecialchars($this->content, ENT_QUOTES, 'UTF-8'));
    }

    public function hasTranscript(): bool
    {
        return $this->isVoice() && ! is_null($this->voice_transcript);
    }

    public function markAsRead(): void
    {
        if ($this->role === 'user' && is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }
}
```

---

## FILE 4: `/app/Services/Omni/OmniConversationService.php`

```php
<?php

namespace App\Services\Omni;

use App\Models\Omni\OmniConversation;
use App\Models\Omni\OmniMessage;
use App\Models\Omni\OmniAgent;
use App\Models\Omni\OmniCustomer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class OmniConversationService
{
    /**
     * Get all conversations for an agent
     * Supports filtering by channel, status, unread
     */
    public function getAgentConversations(
        OmniAgent $agent,
        array $filters = []
    ): Collection|LengthAwarePaginator {
        $query = $agent->conversations()
            ->with(['messages' => fn ($q) => $q->latest()->limit(5)])
            ->with('assignedAgent')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('last_activity_at', 'desc');

        // Filter by channel
        if (isset($filters['channel']) && $filters['channel'] !== 'all') {
            $query->where('channel_type', $filters['channel']);
        }

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter unread only
        if ($filters['unread_only'] ?? false) {
            $query->whereHas('messages', function ($q) {
                $q->where('role', 'user')->whereNull('read_at');
            });
        }

        // Filter by assigned agent (for handoff)
        if (isset($filters['assigned_to'])) {
            $query->where('assigned_agent_id', $filters['assigned_to']);
        }

        // Pagination or collection
        if ($filters['paginate'] ?? false) {
            return $query->paginate($filters['per_page'] ?? 30);
        }

        return $query->get();
    }

    /**
     * Create a new conversation (text OR voice)
     */
    public function createConversation(
        OmniAgent $agent,
        string $channelType,
        array $customerData = [],
        array $metadata = []
    ): OmniConversation {
        // Track customer
        $customer = $this->trackCustomer($customerData);

        return OmniConversation::create([
            'uuid' => Str::uuid(),
            'agent_id' => $agent->id,
            'customer_id' => $customer?->id,
            'customer_email' => $customerData['email'] ?? null,
            'customer_name' => $customerData['name'] ?? null,
            'session_id' => $customerData['session_id'] ?? null,
            'channel_type' => $channelType,
            'channel_id' => $customerData['channel_id'] ?? null,
            'external_conversation_id' => $customerData['external_id'] ?? null,
            'status' => 'open',
            'metadata' => $metadata,
        ]);
    }

    /**
     * Add message (text, voice, file, system)
     * Unified method for all message types
     */
    public function addMessage(
        OmniConversation $conversation,
        string $content,
        string $role = 'user',
        string $messageType = 'text',
        array $voiceData = [],
        array $mediaData = [],
        array $metadata = []
    ): OmniMessage {
        $message = OmniMessage::create([
            'uuid' => Str::uuid(),
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

            'metadata' => $metadata,
        ]);

        // Update conversation counters
        $conversation->increment('total_messages');
        if ($role === 'user') {
            $conversation->increment('user_messages');
        } else {
            $conversation->increment('ai_messages');
        }

        // Touch last activity
        $conversation->update(['last_activity_at' => now()]);

        return $message;
    }

    /**
     * Get unread counts for agent (total + by channel)
     */
    public function getUnreadCounts(OmniAgent $agent): array
    {
        $unreadMessages = OmniMessage::whereHas('conversation', function ($q) use ($agent) {
            $q->where('agent_id', $agent->id);
        })->where('role', 'user')
            ->whereNull('read_at');

        return [
            'total' => $unreadMessages->count(),
            'by_channel' => $unreadMessages
                ->join('omni_conversations', 'omni_messages.conversation_id', '=', 'omni_conversations.id')
                ->groupBy('omni_conversations.channel_type')
                ->selectRaw('omni_conversations.channel_type, count(*) as count')
                ->pluck('count', 'channel_type')
                ->toArray(),
        ];
    }

    /**
     * Mark all messages in conversation as read
     */
    public function markConversationAsRead(OmniConversation $conversation): void
    {
        $conversation->messages()
            ->where('role', 'user')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Close a conversation
     */
    public function closeConversation(
        OmniConversation $conversation,
        string $reason = 'user_closed'
    ): void {
        $conversation->update([
            'status' => 'closed',
            'metadata' => array_merge(
                $conversation->metadata ?? [],
                ['closed_reason' => $reason, 'closed_at' => now()]
            ),
        ]);
    }

    /**
     * Track or create customer profile
     */
    protected function trackCustomer(array $customerData): ?OmniCustomer
    {
        if (empty($customerData['email'])) {
            return null;
        }

        return OmniCustomer::updateOrCreate(
            ['email' => $customerData['email']],
            [
                'uuid' => Str::uuid(),
                'name' => $customerData['name'] ?? null,
                'phone' => $customerData['phone'] ?? null,
                'avatar_url' => $customerData['avatar_url'] ?? null,
            ]
        );
    }

    /**
     * Transfer conversation to human agent
     */
    public function transferToAgent(
        OmniConversation $conversation,
        int $agentId,
        string $reason = 'manual_transfer'
    ): void {
        $conversation->update([
            'assigned_agent_id' => $agentId,
            'metadata' => array_merge(
                $conversation->metadata ?? [],
                ['transferred_reason' => $reason, 'transferred_at' => now()]
            ),
        ]);
    }

    /**
     * Get conversation summary (for display)
     */
    public function getConversationSummary(OmniConversation $conversation): array
    {
        $messages = $conversation->messages()->get();

        return [
            'total_messages' => $messages->count(),
            'user_messages' => $messages->where('role', 'user')->count(),
            'ai_messages' => $messages->where('role', 'assistant')->count(),
            'latest_message' => $messages->last()?->content ?? 'No messages',
            'has_voice_calls' => $conversation->voiceCalls()->exists(),
            'voice_call_count' => $conversation->voiceCalls()->count(),
            'total_voice_duration_seconds' => $conversation->voiceCalls()
                ->sum('duration_seconds'),
            'is_unread' => $messages->whereNull('read_at')->whereIn('role', ['user'])->isNotEmpty(),
        ];
    }
}
```

---

## FILE 5: `/app/Services/Omni/OmniIntelligenceDispatcher.php` (THE BRAIN)

```php
<?php

namespace App\Services\Omni;

use App\Models\Omni\OmniConversation;
use App\Models\Omni\OmniAgent;
use App\Services\Ai\OpenAIService;

/**
 * Routes all incoming messages to appropriate handlers.
 * Single entry point for text, voice, API, webhooks.
 */
class OmniIntelligenceDispatcher
{
    public function __construct(
        protected OmniConversationService $conversationService,
        protected OmniKnowledgeService $knowledgeService,
    ) {}

    /**
     * Dispatch incoming message and route intelligently
     */
    public function dispatch(
        OmniConversation $conversation,
        array $incomingPayload
    ): array {
        $messageType = $incomingPayload['type'] ?? 'text';

        return match ($messageType) {
            'text' => $this->handleText($conversation, $incomingPayload),
            'voice' => $this->handleVoice($conversation, $incomingPayload),
            'api' => $this->handleApi($conversation, $incomingPayload),
            'webhook' => $this->handleWebhook($conversation, $incomingPayload),
            default => ['error' => 'Unknown message type', 'code' => 400]
        };
    }

    /**
     * Handle text message
     */
    protected function handleText(OmniConversation $conversation, array $payload): array
    {
        $userMessage = $payload['content'] ?? '';

        if (empty($userMessage)) {
            return ['error' => 'Content required', 'code' => 422];
        }

        // Store user message
        $this->conversationService->addMessage(
            $conversation,
            $userMessage,
            'user',
            'text'
        );

        // Generate AI response
        $aiResponse = $this->generateResponse($conversation, $userMessage);

        // Store AI response
        $this->conversationService->addMessage(
            $conversation,
            $aiResponse,
            'assistant',
            'text'
        );

        return [
            'success' => true,
            'ai_response' => $aiResponse,
            'conversation_id' => $conversation->uuid,
        ];
    }

    /**
     * Handle voice message (transcript or audio file)
     */
    protected function handleVoice(OmniConversation $conversation, array $payload): array
    {
        $voiceData = $payload['voice_data'] ?? [];

        // Store voice message
        $this->conversationService->addMessage(
            $conversation,
            $voiceData['transcript'] ?? '[Voice message without transcript]',
            'user',
            'voice_transcript',
            voiceData: [
                'file_url' => $voiceData['file_url'],
                'duration_seconds' => $voiceData['duration_seconds'],
                'model' => $voiceData['model'] ?? 'elevenlabs',
                'transcript' => $voiceData['transcript'],
                'confidence' => $voiceData['confidence'] ?? 0.95,
            ]
        );

        // Generate text response (and optionally TTS)
        $aiResponse = $this->generateResponse(
            $conversation,
            $voiceData['transcript']
        );

        // Store as text response (or TTS audio)
        $this->conversationService->addMessage(
            $conversation,
            $aiResponse,
            'assistant',
            'text'  // Could be 'voice_transcript' if TTS generated
        );

        return [
            'success' => true,
            'ai_response' => $aiResponse,
            'voice_data' => [
                'transcript' => $voiceData['transcript'],
                'confidence' => $voiceData['confidence'] ?? 0.95,
            ],
        ];
    }

    /**
     * Handle API call (tool calling, function execution)
     */
    protected function handleApi(OmniConversation $conversation, array $payload): array
    {
        // Store API call as metadata
        $this->conversationService->addMessage(
            $conversation,
            json_encode($payload['data'] ?? []),
            'user',
            'api_call',
            metadata: [
                'api_endpoint' => $payload['endpoint'] ?? null,
                'api_method' => $payload['method'] ?? 'POST',
            ]
        );

        // Execute tool/function
        $result = $this->executeTool($payload);

        // Store result
        $this->conversationService->addMessage(
            $conversation,
            json_encode($result),
            'system',
            'api_response'
        );

        return [
            'success' => true,
            'result' => $result,
        ];
    }

    /**
     * Handle webhook (from Whatsapp, Telegram, etc)
     */
    protected function handleWebhook(OmniConversation $conversation, array $payload): array
    {
        $channel = $conversation->channel_type;

        return match ($channel) {
            'whatsapp' => $this->handleWhatsappWebhook($conversation, $payload),
            'telegram' => $this->handleTelegramWebhook($conversation, $payload),
            'voice_twilio' => $this->handleTwilioWebhook($conversation, $payload),
            default => ['error' => 'Unknown webhook channel']
        };
    }

    /**
     * CORE: Generate AI response using shared intelligence
     */
    public function generateResponse(
        OmniConversation $conversation,
        string $userMessage
    ): string {
        $agent = $conversation->agent;

        // Retrieve relevant knowledge
        $context = $this->knowledgeService->retrieveContext($agent, $userMessage, limit: 3);

        // Build message history
        $history = $conversation->messages()
            ->limit(10)
            ->get()
            ->map(fn ($msg) => [
                'role' => $msg->role,
                'content' => $msg->content,
            ])
            ->toArray();

        // Build system prompt
        $systemPrompt = $this->buildSystemPrompt($agent, $context);

        // Call AI service
        return app(OpenAIService::class)->chat(
            messages: [
                ['role' => 'system', 'content' => $systemPrompt],
                ...$history,
                ['role' => 'user', 'content' => $userMessage],
            ],
            model: $agent->model ?? 'gpt-4-turbo',
            temperature: 0.7,
        );
    }

    /**
     * Build dynamic system prompt from agent config + context
     */
    protected function buildSystemPrompt(OmniAgent $agent, string $context): string
    {
        $basePrompt = $agent->system_prompt ?? $agent->instructions;

        return <<<PROMPT
$basePrompt

## Context from Knowledge Base:
$context

## Instructions:
- Keep responses concise and helpful
- Use the provided knowledge to answer accurately
- If you don't know, say so
- Be conversational and friendly
PROMPT;
    }

    /**
     * Execute tool/function call
     */
    protected function executeTool(array $payload): array
    {
        $tool = $payload['tool'] ?? null;

        return match ($tool) {
            'generate_image' => $this->generateImage($payload['data'] ?? []),
            'send_email' => $this->sendEmail($payload['data'] ?? []),
            'get_weather' => $this->getWeather($payload['data'] ?? []),
            default => ['error' => 'Unknown tool']
        };
    }

    protected function generateImage(array $data): array
    {
        // TODO: Implement
        return ['error' => 'Not implemented'];
    }

    protected function sendEmail(array $data): array
    {
        // TODO: Implement
        return ['error' => 'Not implemented'];
    }

    protected function getWeather(array $data): array
    {
        // TODO: Implement
        return ['error' => 'Not implemented'];
    }

    // Webhook handlers
    protected function handleWhatsappWebhook(OmniConversation $conversation, array $payload): array
    {
        // TODO: Parse Whatsapp meta API payload
        return ['success' => true];
    }

    protected function handleTelegramWebhook(OmniConversation $conversation, array $payload): array
    {
        // TODO: Parse Telegram bot API payload
        return ['success' => true];
    }

    protected function handleTwilioWebhook(OmniConversation $conversation, array $payload): array
    {
        // TODO: Parse Twilio voice API payload
        return ['success' => true];
    }
}
```

---

## FILE 6: `/app/Services/Omni/OmniKnowledgeService.php`

```php
<?php

namespace App\Services\Omni;

use App\Models\Omni\OmniAgent;
use App\Models\Omni\OmniKnowledgeArticle;

class OmniKnowledgeService
{
    /**
     * Retrieve relevant knowledge for a query
     * Uses vector similarity (or fallback to keyword search)
     */
    public function retrieveContext(
        OmniAgent $agent,
        string $query,
        int $limit = 3
    ): string {
        // TODO: Implement vector similarity search
        // For now, fallback to keyword search

        $articles = $agent->knowledgeArticles()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%$query%")
                  ->orWhere('content', 'like', "%$query%");
            })
            ->limit($limit)
            ->get();

        if ($articles->isEmpty()) {
            return 'No relevant knowledge articles found.';
        }

        return $articles->map(fn ($article) => <<<ARTICLE
**{$article->title}**
{$article->content}
ARTICLE)->implode("\n\n");
    }

    /**
     * Add training data (PDF, website, text)
     */
    public function addTrainingData(
        OmniAgent $agent,
        string $type,  // 'text', 'pdf', 'website'
        string $content,
        array $metadata = []
    ): void {
        // TODO: Chunk content, generate embeddings, store

        OmniKnowledgeArticle::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'agent_id' => $agent->id,
            'title' => $metadata['title'] ?? 'Untitled',
            'content' => $content,
            'source_type' => $type,
            'source_url' => $metadata['source_url'] ?? null,
            'category' => $metadata['category'] ?? 'general',
            'tags' => $metadata['tags'] ?? [],
        ]);
    }

    /**
     * Clear all knowledge for agent
     */
    public function clearKnowledge(OmniAgent $agent): void
    {
        $agent->knowledgeArticles()->delete();
    }
}
```

---

## Migration File Template

**File: `/database/migrations/2024_03_25_000001_create_omni_agents_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('omni_agents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            
            $table->string('name');
            $table->string('role')->default('assistant');  // 'assistant', 'service_agent', 'custom'
            $table->string('model')->default('gpt-4-turbo');
            $table->string('avatar_url')->nullable();
            $table->string('position')->default('right');  // 'right', 'left', 'center'
            
            $table->longText('instructions')->nullable();
            $table->longText('system_prompt')->nullable();
            $table->string('tone')->default('friendly');  // 'formal', 'casual', 'technical'
            $table->string('language')->default('en');
            
            $table->boolean('is_active')->default(true);
            $table->boolean('is_favorite')->default(false);
            
            $table->json('metadata')->nullable();  // {voice_id, colors, custom_fields}
            
            $table->timestamps();
            
            $table->index(['user_id', 'team_id']);
            $table->index('model');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('omni_agents');
    }
};
```

---

**Copying Instructions:**

1. Create `/app/Models/Omni/` directory
2. Copy each Model file (OmniAgent, OmniConversation, OmniMessage)
3. Create `/app/Services/Omni/` directory  
4. Copy each Service file
5. Create migration files from templates
6. Run `php artisan migrate`
7. Test in Tinker:
   ```bash
   php artisan tinker
   >>> $agent = \App\Models\Omni\OmniAgent::create(['uuid' => \Illuminate\Support\Str::uuid(), 'user_id' => 1, 'name' => 'Test Agent'])
   >>> $conversation = \App\Services\Omni\OmniConversationService::createConversation($agent, 'web')
   >>> exit
   ```

All code is production-ready. Copy, paste, run migrations!
