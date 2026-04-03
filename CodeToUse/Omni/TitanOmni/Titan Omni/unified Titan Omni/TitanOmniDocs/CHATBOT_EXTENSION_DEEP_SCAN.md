# MagicAI Chatbot Extension Suite: Deep Scan & Titan Integration Strategy

## Executive Summary

Your MagicAI chatbot ecosystem consists of **6 modular extensions** implementing a **federated channel + agent pattern** (v6.1 core). The architecture is production-ready for consolidation into **Titan Command** (business owner control layer), **Titan Go** (field team mobile/voice), and **Titan Nexus** (customer engagement).

---

## PART 1: EXTENSION SUITE ARCHITECTURE

### A. Core Extension Stack

| Extension | Version | Type | Role |
|-----------|---------|------|------|
| **External-Chatbot** | 6.1 | Core | Database, models, AI engine, conversation state |
| **ChatbotAgent** | 2.0 | Control | Human handoff, panel notifications, real-time sync (Ably) |
| **ChatbotVoice** | 2.0 | Channel | Voice/TTS input/output, phone integration |
| **ChatbotTelegram** | - | Channel | Telegram webhook handler, conversation service |
| **ChatbotWhatsapp** | - | Channel | Twilio/WhatsApp integration, message routing |
| **ChatbotMessenger** | - | Channel | Facebook Messenger webhook, media support |

### B. Database Schema (External-Chatbot Core)

**Main Tables:**
```
ext_chatbots (parent entity)
├── id, name, description, ai_model, interaction_type
├── avatar, logo, footer_link
├── is_favorite, is_demo, user_id (owner)
├── created_at, updated_at

ext_chatbot_channels (multi-channel bridge)
├── id, chatbot_id, channel_type (telegram|whatsapp|voice|messenger)
├── channel_config (webhook URLs, API tokens, channel-specific settings)
├── created_at

ext_chatbot_conversations (user sessions)
├── id, chatbot_id, chatbot_customer_id
├── channel_type, last_activity_at
├── connect_agent_at (null = no agent | timestamp = human handoff)
├── pinned, closed, ticket_status, country_code
├── created_at, updated_at

ext_chatbot_histories (message log)
├── id, conversation_id
├── message (text), media_name (attachments)
├── role (user|assistant), model (ai_model used)
├── interaction_type, message_type
├── created_at

ext_chatbot_customers (conversation participants)
├── id, chatbot_id, user_id (null for anonymous)
├── channel_identifier (telegram_id, phone, messenger_psid, etc)
├── created_at

ext_chatbot_embeddings (RAG/vector store)
├── id, chatbot_id, type (pdf|url|text|qa)
├── embedding (vector), content
├── created_at

ext_chatbot_knowledge_bases (training data)
├── id, chatbot_id
├── articles, FAQs, knowledge base sections

ext_chatbot_page_visits (analytics)
├── id, chatbot_id, url, visitor_info, interaction_count
```

**Key Relationships:**
- 1 Chatbot → many Channels (multi-channel aggregation)
- 1 Chatbot → many Conversations (per customer per channel)
- 1 Conversation → many Histories (thread of messages)
- 1 Chatbot → many Customers (cross-channel participant tracking)

---

## PART 2: SERVICE LAYER PATTERNS

### A. Channel Integration Pattern

**All channels follow this contract:**

```php
// ServiceProvider Structure
class ChatbotXxxServiceProvider extends ServiceProvider
{
    boot() {
        $this->registerTranslations()
              ->registerViews()
              ->registerRoutes()        // POST/GET endpoints for webhooks
              ->publishAssets();        // Icons, frontend assets
    }
    
    registerRoutes() {
        // Webhook: /api/v2/chatbot/{chatbotId}/channel/{channelId}/xxx
        // Admin: /dashboard/chatbot-multi-channel/xxx/store
    }
}

// Webhook Controller
class ChatbotXxxWebhookController
{
    handle($chatbotId, $channelId) {
        // Extract channel-specific payload
        // Find/create ChatbotConversation
        // Delegate to ConversationService
    }
}

// Conversation Service
class XxxConversationService
{
    protected $channelId;
    protected $payload;
    protected $conversation;
    
    handleXxx(): void {
        if ($conversation->connect_agent_at) {
            // Agent is connected
            forwardToAgent();
            return;
        }
        
        if (isHumanAgentCommand()) {
            connectToHumanAgent();
            return;
        }
        
        $response = generateResponse($message);
        sendMessage($response);
        insertHistory($conversation, $response);
    }
}

// Channel-Specific Service
class XxxService
{
    sendText($message, $customerId);
    sendMedia($media, $customerId);
    getCustomerChannelId();
}
```

**Channel Webhook Routes:**
```
POST   /api/v2/chatbot/{chatbotId}/channel/{channelId}/telegram
POST   /api/v2/chatbot/{chatbotId}/channel/{channelId}/whatsapp
POST   /api/v2/chatbot/{chatbotId}/channel/{channelId}/messenger
POST   /api/v2/chatbot/{chatbotId}/channel/{channelId}/voice
```

### B. Core Services (External-Chatbot)

**GeneratorService** (AI response generation)
```php
class GeneratorService
{
    generate(string $message): string
    {
        // 1. Retrieve embeddings (RAG/vector search)
        // 2. Build context from knowledge base
        // 3. Call AI model (OpenAI/Claude/etc)
        // 4. Return response
    }
}
```

**ChatbotService** (CRUD, configuration)
```php
class ChatbotService
{
    createChatbot($data);
    updateChatbot($id, $data);
    getChatbot($id);
    // Configuration: AI model, interaction type, welcome message, etc
}
```

**ChatbotAnalyticsService** (metrics & reporting)
```php
class ChatbotAnalyticsService
{
    getConversationMetrics($chatbotId);
    getChannelStats($chatbotId);
    getAgentPerformance($chatbotId);
}
```

**ConversationExportService** (data extraction)
```php
class ConversationExportService
{
    export($conversationId, $format = 'json|csv');
}
```

### C. Agent Control Layer (ChatbotAgent)

**Real-time Sync via Ably** (WebSocket fallback)
```php
class ChatbotForPanelEventAbly
{
    // Pushes events to dashboard when:
    // - New conversation arrives
    // - Customer message received
    // - Agent takes over
    // - Conversation closed
}
```

**Routes:**
```
GET    /dashboard/chatbot-agent                       → index
GET    /dashboard/chatbot-agent/notification/count    → poll for new convos
GET    /dashboard/chatbot-agent/conversations         → list all
PUT    /dashboard/chatbot-agent/conversations         → update conversation state
POST   /dashboard/chatbot-agent/conversations/name    → rename conversation
POST   /dashboard/chatbot-agent/conversations/pinned  → pin/unpin
POST   /dashboard/chatbot-agent/conversations/closed  → close/reopen
POST   /dashboard/chatbot-agent/history               → agent sends message
DELETE /dashboard/chatbot-agent/destroy               → delete conversation
```

---

## PART 3: DATA FLOW & STATE MACHINE

### A. Conversation Lifecycle

```
1. INITIATE
   Webhook arrives (Telegram/WhatsApp/Voice/etc)
   → Find ChatbotChannel by {chatbotId, channelId}
   → Create ChatbotConversation (if new customer)
   → Create ChatbotCustomer record (if new channel+customer combo)

2. PROCESS
   if connect_agent_at == null:
       → Generate AI response via GeneratorService
       → Log to ChatbotHistory
       → Send via ChannelService
   else:
       → Forward to agent (if available) or queue

3. HANDOFF (if agent takes over)
   → Set conversation.connect_agent_at = now()
   → Push event to ChatbotAgent dashboard via Ably
   → Agent reads messages from ChatbotHistory
   → Agent sends reply via ChatbotAgentController → ChannelService
   → Loop continues with agent in loop

4. CLOSE
   → Set conversation.closed = true
   → Send closing message
   → Log ticket_status if applicable
```

### B. Message Flow (Voice Example)

```
Customer calls → Twilio/Phone Provider
    ↓
ChatbotVoice webhook (/api/v2/chatbot/{id}/channel/{channelId}/voice)
    ↓
ChatbotVoiceWebhookController::handle()
    ↓
VoiceConversationService::handleVoice()
    ├─ Speech-to-text (if input is audio)
    ├─ Find/create ChatbotConversation
    ├─ Check if agent connected
    ├─ GeneratorService::generate($speechText)
    ├─ Text-to-speech (output response)
    └─ Send via VoiceService::sendAudio()
    ↓
ChatbotHistory logged
    ↓
Customer receives audio response
```

---

## PART 4: TITAN INTEGRATION STRATEGY

### A. Architecture: Three Tier System

```
┌─────────────────────────────────────────────────────────────┐
│                    TITAN NEXUS (Customer)                   │
│         • Unified messaging inbox (all channels)            │
│         • Voice/text conversation history                   │
│         • Service request tracking (tickets)                │
│         • Customer self-service portal                      │
│         • Mobile-first responsive UI                        │
└──────────────────────┬──────────────────────────────────────┘
                       ↓
┌─────────────────────────────────────────────────────────────┐
│              TITAN COMMAND (Business Owner)                 │
│         • Multi-business operation center                   │
│         • Agent assignment & queue management               │
│         • Chatbot configuration & training                  │
│         • Analytics dashboard & reporting                   │
│         • Team performance metrics                          │
│         • Billing & usage tracking                          │
└──────────────────────┬──────────────────────────────────────┘
                       ↓
┌─────────────────────────────────────────────────────────────┐
│               TITAN GO (Field & Mobile)                     │
│         • Offline-capable mobile app                        │
│         • Real-time agent notifications                     │
│         • Voice-first conversation handling                 │
│         • Quick-reply & template management                 │
│         • Geofence-triggered automation                     │
│         • Battery/device-aware sync                         │
└─────────────────────────────────────────────────────────────┘
```

### B. Consolidation: Data Model Mapping

**Current Structure (MagicAI) → Titan Structure**

```
ext_chatbots
├─ belongs_to User (business owner)
│  └─ MAPS TO: TitanZero.business_id
│
├─ channel_type (telegram|whatsapp|voice|messenger)
│  └─ MAPS TO: TitanNexus.customer_channel_id
│
├─ ai_model (openai|claude|etc)
│  └─ MAPS TO: TitanZero routing (LogiCore → CreatiCore → OmegaCore)
│
└─ interaction_type (SMART_SWITCH | LINEAR)
   └─ MAPS TO: TitanCommand.automation_rule

ext_chatbot_conversations
├─ chatbot_id + chatbot_customer_id
│  └─ MAPS TO: TitanNexus.conversation_id
│
├─ connect_agent_at
│  └─ MAPS TO: TitanCommand.agent_id (owner/staff member)
│
├─ last_activity_at
│  └─ MAPS TO: TitanNexus.last_event_at
│
└─ ticket_status
   └─ MAPS TO: TitanMoney.service_request.status

ext_chatbot_histories
├─ message + role (user|assistant)
│  └─ MAPS TO: TitanNexus.message_thread
│
├─ model (ai_model that generated it)
│  └─ MAPS TO: Rewind Core (Titan BOS audit trail)
│
└─ interaction_type
   └─ MAPS TO: TitanWork.activity_log.type
```

### C. Integration Points

#### 1. **TITAN COMMAND** (Owner Control)

**New Routes:**
```
GET    /dashboard/titan-command/chatbots              → List all chatbots (multi-channel)
POST   /dashboard/titan-command/chatbots              → Create new chatbot
PUT    /dashboard/titan-command/chatbots/{id}        → Update config
DELETE /dashboard/titan-command/chatbots/{id}        → Delete chatbot

GET    /dashboard/titan-command/conversations         → All agent conversations
GET    /dashboard/titan-command/conversations/{id}   → Conversation detail + history
PUT    /dashboard/titan-command/conversations/{id}   → Reassign agent, update status
POST   /dashboard/titan-command/conversations/{id}/handoff → Force agent transfer

GET    /dashboard/titan-command/analytics            → Dashboard metrics
GET    /dashboard/titan-command/analytics/channels   → Performance by channel
GET    /dashboard/titan-command/analytics/agents     → Agent KPIs

GET    /dashboard/titan-command/knowledge-base       → Training data management
POST   /dashboard/titan-command/knowledge-base       → Upload PDFs, URLs, Q&A
PUT    /dashboard/titan-command/knowledge-base/{id}  → Update article

GET    /dashboard/titan-command/team                 → Agent roster
POST   /dashboard/titan-command/team/{agentId}/assign → Assign to chatbot
```

**Business Logic:**
```php
// TitanCommand controls:
// 1. Which chatbots are active (enable/disable per business)
// 2. AI model routing per chatbot (claude vs openai vs local)
// 3. Agent queue assignment (round-robin vs skill-based)
// 4. SLA thresholds (response time, handoff timing)
// 5. Knowledge base sync (from PDF, website, Notion, etc)
// 6. Billing aggregation (by chatbot, by agent, by channel)

class TitanCommandChatbotController
{
    public function index() {
        // Only show chatbots owned by Auth::user()->business_id
        return Chatbot::whereBelongsTo(auth()->user()->business)
                      ->with('conversations', 'analytics')
                      ->get();
    }
    
    public function store(StoreChatbotRequest $request) {
        // Create chatbot + default channels
        $chatbot = Chatbot::create([
            'business_id' => auth()->user()->business_id,
            'name' => $request->name,
            'ai_model' => $request->ai_model ?? 'claude-sonnet',
            'interaction_type' => 'SMART_SWITCH',
        ]);
        
        // Initialize channels
        foreach (['telegram', 'whatsapp', 'voice', 'messenger'] as $channel) {
            ChatbotChannel::create([
                'chatbot_id' => $chatbot->id,
                'channel_type' => $channel,
                'channel_config' => [],
            ]);
        }
        
        return $chatbot;
    }
    
    public function update(UpdateChatbotRequest $request, Chatbot $chatbot) {
        $this->authorize('update', $chatbot);
        
        $chatbot->update($request->validated());
        
        // Trigger retraining if knowledge base updated
        if ($request->has('knowledge_base_ids')) {
            GeneratorService::trainOnKnowledgeBase($chatbot);
        }
        
        return $chatbot;
    }
}
```

#### 2. **TITAN GO** (Field/Mobile Agent)

**New Routes (API only, JSON responses):**
```
GET    /api/v2/titan-go/conversations                 → New + pending conversations
GET    /api/v2/titan-go/conversations/{id}            → Convo detail + full history
POST   /api/v2/titan-go/conversations/{id}/reply      → Agent sends message
POST   /api/v2/titan-go/conversations/{id}/transfer   → Transfer to other agent
POST   /api/v2/titan-go/conversations/{id}/close      → Mark resolved

GET    /api/v2/titan-go/notifications                 → Poll for new messages
POST   /api/v2/titan-go/notifications/ack             → Mark as read

POST   /api/v2/titan-go/voice/start                   → Initiate voice call
POST   /api/v2/titan-go/voice/end                     → End voice call

GET    /api/v2/titan-go/templates                     → Quick-reply templates
GET    /api/v2/titan-go/team-members                  → Available agents for transfer

GET    /api/v2/titan-go/device-info                   → Geolocation, battery, network
```

**Business Logic (Mobile-First):**
```php
class TitanGoConversationController
{
    public function index() {
        // Return only conversations assigned to this agent
        $agent = auth()->guard('api')->user();
        
        return ChatbotConversation::where('assigned_agent_id', $agent->id)
                                   ->where('connected_agent_at', '!=', null)
                                   ->orderBy('last_activity_at', 'desc')
                                   ->paginate();
    }
    
    public function reply(Request $request, ChatbotConversation $conversation) {
        // Agent types reply
        $message = $request->input('message');
        $attachments = $request->file('attachments'); // Photos, voice, etc
        
        // Log to ChatbotHistory
        ChatbotHistory::create([
            'conversation_id' => $conversation->id,
            'message' => $message,
            'media_name' => $attachments ? $attachments->store() : null,
            'role' => 'assistant',
            'model' => 'human-agent',
        ]);
        
        // Send via appropriate channel
        $this->sendViaChannel($conversation, $message, $attachments);
        
        // Notify customer (Ably push)
        ChatbotForPanelEventAbly::pushToCustomer($conversation, $message);
    }
    
    public function transfer(Request $request, ChatbotConversation $conversation) {
        // Hand off to another agent
        $newAgent = User::find($request->input('agent_id'));
        
        $conversation->update(['assigned_agent_id' => $newAgent->id]);
        
        // Notify new agent (real-time Ably event)
        ChatbotForPanelEventAbly::notifyAgent($newAgent, $conversation);
    }
}
```

#### 3. **TITAN NEXUS** (Customer Portal)

**New Routes (Public/Authenticated):**
```
GET    /titan-nexus/conversations                     → My conversations (across channels)
GET    /titan-nexus/conversations/{id}                → My conversation detail
POST   /titan-nexus/conversations/{id}/message        → Send message

GET    /titan-nexus/service-requests                  → My service tickets
POST   /titan-nexus/service-requests                  → Create new request (auto-routes to chatbot)

GET    /titan-nexus/profile                           → My contact info, preferences
PUT    /titan-nexus/profile                           → Update preferences

GET    /titan-nexus/{business}/chatbot                → Public chatbot widget (embed)
```

**Business Logic (Customer Self-Service):**
```php
class TitanNexusConversationController
{
    public function index() {
        // Show all conversations for authenticated customer
        $customer = auth()->user(); // or guest with session cookie
        
        return ChatbotConversation::where('chatbot_customer_id', $customer->id)
                                   ->orderBy('last_activity_at', 'desc')
                                   ->get();
    }
    
    public function show(ChatbotConversation $conversation) {
        // Full thread for this customer only
        return [
            'conversation' => $conversation,
            'messages' => ChatbotHistory::where('conversation_id', $conversation->id)
                                         ->paginate(),
            'assigned_agent' => $conversation->assignedAgent, // If agent connected
            'status' => $conversation->closed ? 'closed' : 'open',
        ];
    }
    
    public function store(StoreMessageRequest $request) {
        // Customer sends message
        $conversation = ChatbotConversation::findOrCreate(...);
        
        ChatbotHistory::create([
            'conversation_id' => $conversation->id,
            'message' => $request->input('message'),
            'role' => 'user',
            'model' => null,
        ]);
        
        // Trigger response (AI or agent)
        if ($conversation->assigned_agent_id) {
            // Notify agent
            ChatbotForPanelEventAbly::notifyAgent($conversation->assignedAgent, $conversation);
        } else {
            // Generate AI response
            $response = GeneratorService::generate($request->input('message'));
            // (Response workflow same as before)
        }
    }
}
```

---

## PART 5: WORKSUITE API INTEGRATION

### A. WorkSuite Models to Sync

**From TitanZero to ChatBot models:**

```php
// User → Agent (ChatbotAgent)
TitanZero.User
├─ business_id
├─ email
└─ role (owner|staff|agent)
   ↓
ChatbotAgent.Agent
├─ user_id (foreign key)
├─ business_id
├─ response_time_avg
└─ satisfaction_score

// Business → Chatbot Namespace
TitanZero.Business
├─ id
├─ name
└─ settings
   ↓
Chatbot.business_id (scope)
├─ ext_chatbots
├─ ext_chatbot_conversations
└─ ext_chatbot_channels

// Service Request → Conversation Link
TitanMoney.ServiceRequest
├─ id
├─ customer_id
├─ status
├─ assigned_to
   ↓
ChatbotConversation.ticket_id (foreign key)
├─ conversation_id
├─ service_request_id
├─ status (synced)
└─ auto_create_on_issue_detected
```

### B. WorkSuite API Endpoints to Leverage

```php
// WorkSuite routes (assuming WorkSuite is the parent app)

// 1. Get business chatbot config
GET /api/v1/workspaces/{business_id}/chatbot-config

// 2. Create service request from chatbot escalation
POST /api/v1/workspaces/{business_id}/service-requests
{
    "customer_id": "...",
    "title": "Chatbot escalation: ...",
    "description": "Conversation ID: ...",
    "source": "chatbot",
    "priority": "medium",
    "tags": ["chatbot", "escalated"]
}

// 3. Update service request when agent resolves
PUT /api/v1/workspaces/{business_id}/service-requests/{request_id}
{
    "status": "resolved",
    "resolution_notes": "Handled in chatbot conversation {convo_id}"
}

// 4. Get agent availability (for routing)
GET /api/v1/workspaces/{business_id}/agents/availability
Response: [
    { "agent_id": 123, "is_online": true, "active_conversations": 2 }
]

// 5. Log interaction for analytics
POST /api/v1/workspaces/{business_id}/activity-log
{
    "type": "chatbot_interaction",
    "chatbot_id": "...",
    "conversation_id": "...",
    "duration_seconds": 45,
    "resolved": true
}
```

### C. Middleware Bridge

```php
// New service: ChatbotWorkSuiteService
class ChatbotWorkSuiteService
{
    public function escalateToServiceRequest(
        ChatbotConversation $conversation
    ): void {
        $customer = $conversation->customer;
        
        $serviceRequest = ServiceRequest::create([
            'workspace_id' => $conversation->chatbot->business_id,
            'customer_id' => $customer->user_id,
            'title' => "Chatbot Escalation: {$conversation->chatbot->name}",
            'description' => $this->generateSummary($conversation),
            'source' => 'chatbot',
            'priority' => 'medium',
            'chatbot_conversation_id' => $conversation->id,
        ]);
        
        // Update conversation link
        $conversation->update([
            'service_request_id' => $serviceRequest->id,
        ]);
        
        // Notify assigned agent
        $agent = $this->getAvailableAgent($conversation->chatbot->business_id);
        if ($agent) {
            $serviceRequest->assignTo($agent);
        }
    }
    
    public function logInteraction(ChatbotConversation $conversation): void {
        // Push analytics back to WorkSuite
        activityLog()->create([
            'workspace_id' => $conversation->chatbot->business_id,
            'type' => 'chatbot_interaction',
            'metadata' => [
                'chatbot_id' => $conversation->chatbot_id,
                'channel' => $conversation->channel_type,
                'duration_seconds' => $conversation->created_at->diffInSeconds(now()),
                'messages_exchanged' => $conversation->histories()->count(),
                'resolved_by' => $conversation->assigned_agent_id ? 'agent' : 'ai',
            ],
        ]);
    }
    
    public function syncAgentAvailability(): void {
        // Periodically sync WorkSuite agent status to chatbot router
        $agents = Agent::whereWorkspaceId(auth()->user()->business_id)
                       ->with('availability')
                       ->get();
        
        cache()->put(
            "chatbot:agents:{$business_id}:availability",
            $agents->pluck('is_available', 'id'),
            minutes: 1
        );
    }
}
```

---

## PART 6: IMPLEMENTATION ROADMAP

### Phase 1: Foundation (Weeks 1-2)

1. **Create Titan adapter layer** (no changes to MagicAI extensions yet)
   ```
   /app/TitanAdapters/
   ├─ ChatbotCommandAdapter.php      (routes to Titan Command)
   ├─ ChatbotGoAdapter.php            (routes to Titan Go API)
   ├─ ChatbotNexusAdapter.php         (routes to customer portal)
   └─ WorkSuiteIntegrationService.php (sync with WorkSuite)
   ```

2. **Database migrations** for new tables
   ```
   - Add business_id to chatbots (multi-tenancy)
   - Add assigned_agent_id to conversations
   - Add service_request_id to conversations
   - Add ticket_status sync fields
   ```

3. **API routes** (new namespaces, no touching existing)
   ```
   /routes/titan-command.php
   /routes/titan-go.php
   /routes/titan-nexus.php
   ```

### Phase 2: Integration (Weeks 3-4)

4. **Titan Command Controller** (owner dashboard)
   - List all business chatbots
   - Configure AI model per chatbot
   - Manage agent assignments
   - View analytics dashboard

5. **Titan Go API** (mobile-first agent app)
   - Conversation polling
   - Real-time message handling
   - Voice call integration
   - Offline queuing

6. **Titan Nexus Portal** (customer self-service)
   - Conversation history
   - New request creation
   - Status tracking
   - Self-service search

### Phase 3: Sync (Weeks 5-6)

7. **WorkSuite bridge**
   - Bidirectional sync (conversations ↔ service requests)
   - Agent status sync
   - Analytics aggregation
   - Revenue/billing tracking

8. **Real-time notifications** (Ably optimization)
   - Owner dashboard (new convos, escalations)
   - Agent app (message alerts, routing)
   - Customer portal (agent response, status)

### Phase 4: Launch (Week 7+)

9. **Testing & documentation**
   - Integration tests (channel → Titan flow)
   - Load testing (multi-business, multi-agent)
   - User guides for each tier

10. **Deployment**
    - Feature flag (enable per business)
    - Rollback plan
    - Monitoring & alerting

---

## PART 7: KEY ARCHITECTURAL DECISIONS

### A. Multi-Tenancy Scope

**Decision:** Add `business_id` to `ext_chatbots` table (foreign key to `workspaces` or similar)

**Rationale:**
- TitanZero is multi-tenant (per business)
- Each chatbot = 1 business (not 1 user)
- Allows team members to collaborate on same chatbot
- Enables shared agent queues

```sql
ALTER TABLE ext_chatbots ADD COLUMN business_id BIGINT UNSIGNED NOT NULL AFTER user_id;
ALTER TABLE ext_chatbots ADD FOREIGN KEY (business_id) REFERENCES workspaces(id) ON DELETE CASCADE;
```

### B. Agent Routing Algorithm

**Decision:** Queue-based with skill matching

```php
class AgentRoutingService
{
    public function assignToAvailableAgent(ChatbotConversation $conversation): ?Agent
    {
        $agents = Agent::whereBusinessId($conversation->chatbot->business_id)
                       ->where('is_online', true)
                       ->orderBy('active_conversation_count', 'asc') // Round-robin
                       ->first();
        
        if (!$agents) {
            // Queue conversation, notify when agent online
            $conversation->update(['queued_at' => now()]);
            broadcast(new ConversationQueued($conversation));
            return null;
        }
        
        $conversation->update([
            'assigned_agent_id' => $agents->id,
            'connected_agent_at' => now(),
        ]);
        
        return $agents;
    }
}
```

### C. AI Model Routing (per Titan Nexus specs)

**Decision:** Sequential pipeline (LogiCore → CreatiCore → OmegaCore)

```php
class GeneratorService
{
    public function generate(string $userMessage, Chatbot $chatbot): string
    {
        // 1. LogiCore: Intent extraction + parsing
        $intent = $this->callModel('o3', "Extract intent from: $userMessage");
        
        // 2. CreatiCore: Response generation (creative, empathetic)
        $draftResponse = $this->callModel('claude-sonnet', 
            "Generate helpful response for intent: $intent"
        );
        
        // 3. OmegaCore: Verification + safety check
        $finalResponse = $this->callModel('claude-opus',
            "Review and refine for safety/accuracy: $draftResponse"
        );
        
        return $finalResponse;
    }
    
    private function callModel(string $model, string $prompt): string
    {
        // Route to Anthropic, OpenAI, or local LLM
        return match($model) {
            'claude-sonnet' => \Anthropic\Facades\Claude::sonnet()->message($prompt),
            'claude-opus' => \Anthropic\Facades\Claude::opus()->message($prompt),
            'o3' => // OpenAI call
        };
    }
}
```

### D. Knowledge Base Sync

**Decision:** Event-driven sync on update

```php
class KnowledgeBaseUpdateObserver
{
    public function updated(ChatbotKnowledgeBase $kb): void
    {
        // Re-embed articles when KB updated
        foreach ($kb->articles as $article) {
            EmbedingService::embed(
                chatbotId: $kb->chatbot_id,
                type: 'kb_article',
                content: $article->content,
                metadata: ['source' => 'knowledge_base', 'article_id' => $article->id]
            );
        }
        
        // Clear GeneratorService cache
        cache()->forget("chatbot:{$kb->chatbot_id}:embeddings");
        
        // Log for analytics
        activityLog()->create([
            'type' => 'knowledge_base_updated',
            'chatbot_id' => $kb->chatbot_id,
            'article_count' => $kb->articles()->count(),
        ]);
    }
}
```

---

## PART 8: CODE EXAMPLES

### Example 1: Create Titan Command Chatbot

```php
// POST /dashboard/titan-command/chatbots
// Request:
{
    "name": "CustomerSupport-CleanSmart",
    "description": "Multi-channel support for CleanSmart service customers",
    "ai_model": "claude-sonnet",
    "enable_channels": ["telegram", "whatsapp", "voice"],
    "knowledge_base_urls": [
        "https://help.cleansmart.art/faq",
        "https://cleansmart.art/pricing"
    ]
}

// Controller:
class TitanCommandChatbotController
{
    public function store(StoreChatbotRequest $request): JsonResponse
    {
        $chatbot = DB::transaction(function () use ($request) {
            // Create chatbot
            $chatbot = Chatbot::create([
                'business_id' => auth()->user()->business_id,
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'ai_model' => $request->input('ai_model', 'claude-sonnet'),
                'interaction_type' => InteractionType::SMART_SWITCH,
                'welcome_message' => 'Hi! How can I help you today?',
                'user_id' => auth()->id(),
            ]);
            
            // Create channels
            foreach ($request->input('enable_channels', ['telegram', 'whatsapp']) as $channel) {
                ChatbotChannel::create([
                    'chatbot_id' => $chatbot->id,
                    'channel_type' => $channel,
                    'channel_config' => json_encode([
                        'webhook_url' => route('api.v2.chatbot.channel.' . $channel . '.post.handle', [
                            'chatbotId' => $chatbot->id,
                            'channelId' => $channel,
                        ]),
                        'is_active' => true,
                    ]),
                ]);
            }
            
            // Add knowledge base
            foreach ($request->input('knowledge_base_urls', []) as $url) {
                EmbedingService::embedUrl(
                    chatbotId: $chatbot->id,
                    url: $url,
                    type: 'url'
                );
            }
            
            return $chatbot;
        });
        
        return response()->json([
            'success' => true,
            'chatbot' => $chatbot->load('channels'),
            'webhook_urls' => $this->getWebhookUrls($chatbot),
        ]);
    }
}
```

### Example 2: Titan Go Agent App Handler

```php
// GET /api/v2/titan-go/conversations
class TitanGoConversationController
{
    public function index(): JsonResponse
    {
        $agent = auth()->guard('api')->user(); // Mobile agent
        
        $conversations = ChatbotConversation::with([
            'chatbot:id,name,avatar',
            'customer:id,channel_identifier',
            'histories:id,message,role,created_at'
        ])
        ->where('assigned_agent_id', $agent->id)
        ->where('connect_agent_at', '!=', null)
        ->where('closed', false)
        ->orderBy('last_activity_at', 'desc')
        ->paginate(20);
        
        return response()->json($conversations);
    }
    
    // POST /api/v2/titan-go/conversations/{id}/reply
    public function reply(Request $request, ChatbotConversation $conversation): JsonResponse
    {
        $agent = auth()->guard('api')->user();
        
        // Verify agent owns this conversation
        $this->authorize('reply', $conversation, $agent);
        
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'attachment' => 'nullable|file|max:50000', // Voice, image, etc
        ]);
        
        DB::transaction(function () use ($conversation, $validated, $agent) {
            // Log message
            $history = ChatbotHistory::create([
                'conversation_id' => $conversation->id,
                'message' => $validated['message'],
                'media_name' => $validated['attachment'] ? $this->storeAttachment($validated['attachment']) : null,
                'role' => 'assistant',
                'model' => 'human-agent',
                'interaction_type' => InteractionType::AGENT_REPLY,
            ]);
            
            // Send via channel
            $this->sendViaChannel(
                $conversation,
                $validated['message'],
                $validated['attachment'] ?? null
            );
            
            // Update conversation timestamp
            $conversation->update(['last_activity_at' => now()]);
            
            // Log for analytics
            activityLog()->create([
                'type' => 'agent_reply',
                'agent_id' => $agent->id,
                'conversation_id' => $conversation->id,
            ]);
        });
        
        // Notify customer (Ably event)
        broadcast(new MessageSent($conversation, $history));
        
        return response()->json(['success' => true, 'message' => $history]);
    }
    
    private function sendViaChannel(
        ChatbotConversation $conversation,
        string $message,
        ?UploadedFile $attachment = null
    ): void {
        $channel = $conversation->channel;
        
        match($channel->channel_type) {
            'telegram' => app(TelegramService::class)->setChannel($channel)
                ->sendText($message, $conversation->customer->channel_identifier),
            'whatsapp' => app(TwilioService::class)->setChannel($channel)
                ->sendText($message, $conversation->customer->channel_identifier),
            'voice' => app(VoiceService::class)->setChannel($channel)
                ->sendAudio($message, $conversation->customer->channel_identifier),
            'messenger' => app(MessengerService::class)->setChannel($channel)
                ->sendText($message, $conversation->customer->channel_identifier),
        };
    }
}
```

### Example 3: Titan Nexus Customer Portal

```php
// GET /titan-nexus/conversations
class TitanNexusConversationController
{
    public function index(): View
    {
        $customer = auth()->user(); // Or guest with cookie
        
        $conversations = ChatbotConversation::where('chatbot_customer_id', $customer->id)
            ->with([
                'chatbot:id,name,avatar',
                'histories' => fn($q) => $q->latest()->limit(1),
                'assignedAgent:id,name,email'
            ])
            ->orderBy('last_activity_at', 'desc')
            ->paginate();
        
        return view('titan-nexus.conversations.index', [
            'conversations' => $conversations,
            'unread_count' => ChatbotHistory::whereHas('conversation', fn($q) => 
                $q->where('chatbot_customer_id', $customer->id)
                  ->where('closed', false)
            )->where('role', 'assistant')
             ->where('read_at', null)
             ->count(),
        ]);
    }
    
    // GET /titan-nexus/conversations/{id}
    public function show(ChatbotConversation $conversation): View
    {
        $this->authorize('view', $conversation); // Customer can only see own
        
        $messages = ChatbotHistory::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->paginate(50);
        
        // Mark as read
        ChatbotHistory::where('conversation_id', $conversation->id)
            ->where('role', 'assistant')
            ->where('read_at', null)
            ->update(['read_at' => now()]);
        
        return view('titan-nexus.conversations.show', [
            'conversation' => $conversation,
            'messages' => $messages,
            'agent' => $conversation->assignedAgent,
            'status' => $conversation->closed ? 'closed' : 'open',
            'estimated_response_time' => $this->getEstimatedResponseTime($conversation),
        ]);
    }
    
    // POST /titan-nexus/conversations/{id}/message
    public function sendMessage(Request $request, ChatbotConversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);
        
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);
        
        if ($conversation->closed) {
            return response()->json(['error' => 'Conversation is closed'], 400);
        }
        
        // Create message
        $history = ChatbotHistory::create([
            'conversation_id' => $conversation->id,
            'message' => $validated['message'],
            'role' => 'user',
            'interaction_type' => InteractionType::CUSTOMER_MESSAGE,
        ]);
        
        $conversation->update(['last_activity_at' => now()]);
        
        // Trigger response
        if ($conversation->assigned_agent_id) {
            // Notify assigned agent (Ably)
            broadcast(new CustomerMessageReceived($conversation, $history));
        } else {
            // Generate AI response
            $response = app(GeneratorService::class)->generate(
                $validated['message'],
                $conversation->chatbot
            );
            
            ChatbotHistory::create([
                'conversation_id' => $conversation->id,
                'message' => $response,
                'role' => 'assistant',
                'model' => $conversation->chatbot->ai_model,
            ]);
        }
        
        return response()->json(['success' => true, 'message' => $history]);
    }
}
```

---

## PART 9: SUMMARY & NEXT STEPS

### What You Have
✅ Production-grade MagicAI chatbot system (6 extensions, 8+ services)  
✅ Multi-channel integration (Telegram, WhatsApp, Voice, Messenger, Agent Panel)  
✅ Database schema ready for consolidation (business_id, agent routing, analytics)  
✅ Service patterns aligned with Titan architecture (modular, event-driven)

### What You Need to Build
1. **Titan Command adapter** (owner dashboard, analytics)
2. **Titan Go mobile API** (real-time agent handling)
3. **Titan Nexus customer portal** (self-service + history)
4. **WorkSuite integration** (bidirectional sync)
5. **Multi-tenancy layer** (business isolation, team permissions)

### Recommended Implementation Order
1. Add `business_id` + `assigned_agent_id` to chatbot models (migrations)
2. Build TitanCommandChatbotController (CRUD + analytics)
3. Build TitanGoConversationController (API for mobile agents)
4. Build TitanNexusConversationController (customer portal)
5. Implement WorkSuiteIntegrationService (sync + events)
6. Add real-time Ably routing (existing + enhanced)
7. Testing, docs, launch

### Expected Outcome
- **TitanZero** = Unified business OS (owns all 3 layers)
- **Titan Command** = Multi-business owner control
- **Titan Go** = Field-ready mobile agent app
- **Titan Nexus** = Customer self-service portal
- **MagicAI chatbots** = Underlying execution engine (transparent)

---

**Next move:** Would you like me to scaffold the controller code, database migrations, or dive deeper into any specific component?
