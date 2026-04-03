# Single Database Architecture: External Chatbots as Native MagicAI Feature

## The Realization

You don't need to "integrate" the chatbot extension into MagicAI at all.

**The chatbot extension IS already part of MagicAI.**

It already:
- ✅ Uses MagicAI's database
- ✅ Uses MagicAI's authentication  
- ✅ Uses MagicAI's user/workspace structure
- ✅ Uses MagicAI's permission system

The extension is **already unified.**

---

## Current State (What You Have Now)

```
MagicAI (Parent App)
├─ Database: workspaces, users, teams, service_requests, etc
│
└─ Extensions (Marketplace):
   ├─ Chatbot (External-Chatbot.zip) ← Already in same DB
   ├─ ChatbotAgent
   ├─ ChatbotTelegram
   ├─ ChatbotWhatsapp
   ├─ ChatbotMessenger
   └─ ChatbotVoice
```

**All tables live in the same MagicAI database:**

```sql
-- WorkSuite tables (existing)
workspaces
users
teams
service_requests
permissions
roles
activity_logs

-- Chatbot tables (extension, same DB)
ext_chatbots
ext_chatbot_channels
ext_chatbot_conversations
ext_chatbot_histories
ext_chatbot_customers
ext_chatbot_embeddings
ext_chatbot_knowledge_bases
ext_chatbot_page_visits
ext_chatbot_channel_webhooks
```

---

## What You're Actually Asking

**"Can I make the chatbot extension talk to WorkSuite without separate integration logic?"**

**Answer: It already does.**

The question is: **How do we expose the Chatbot features through different dashboards (owner/agent/customer) without building separate apps?**

**The answer: You don't need separate apps. Just different views/controllers in MagicAI.**

---

## SIMPLEST POSSIBLE ARCHITECTURE

### Step 1: One Database (Already True)
```php
// Same database for everything
config/database.php
├─ connection: mysql (or whatever)
├─ database: magicai_production
└─ All tables share this database
```

### Step 2: WorkSuite Routes Already Exist
```php
// MagicAI's existing routing system
routes/web.php (authenticated)
├─ /dashboard/*           ← Owner dashboard
├─ /team/*                ← Team views  
├─ /api/v1/*              ← API routes

// Just add to this:
├─ /dashboard/chatbots/*     ← ChatBot owner controls
├─ /dashboard/agent-panel/*  ← ChatBot agent handles
└─ /customer-portal/*        ← ChatBot customer self-service
```

### Step 3: Reuse MagicAI's Auth
```php
// MagicAI auth middleware (already exists)
middleware:
├─ auth              (is user logged in?)
├─ verified          (is email verified?)
├─ workspace.active  (does workspace exist?)
└─ can:*             (does user have permission?)

// ChatBot just uses these
Route::middleware(['auth', 'verified', 'workspace.active'])
    ->group(function() {
        // ChatBot routes already scoped to authenticated user's workspace
    });
```

### Step 4: That's It

```
No separate apps.
No separate databases.
No data sync.
No SSO complexity.

Just:
├─ MagicAI (one app)
├─ Database (one instance)
├─ Routes (add a few new controllers)
└─ Views (render different UIs based on role)
```

---

## MAPPING: HOW IT WORKS

### Current Extension Structure (What You Have)

```php
// ChatbotServiceProvider.php (already in MagicAI)
class ChatbotServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerRoutes()
            ->registerMigrations()
            ->registerViews();
    }
    
    private function registerRoutes(): static
    {
        Route::middleware(['web', 'auth'])
            ->group(function() {
                // These routes ALREADY run in MagicAI context
                // They already use MagicAI's auth
                // They already have access to auth()->user()
                // They already scope by auth()->user()->workspace_id
            });
        
        return $this;
    }
}
```

**The extension is NOT separate. It's a MODULE in MagicAI.**

### What You Need to Add

**Currently, MagicAI shows chatbot config in:**
```
/dashboard/chatbot-agent/conversations
/dashboard/chatbot-multi-channel/telegram
/dashboard/chatbot-multi-channel/whatsapp
```

**You want to reorganize this to:**

```
/dashboard/chatbots                  (Owner) - Titan Command
/dashboard/agent/conversations       (Agent) - Titan Go  
/customer-portal/conversations       (Customer) - Titan Nexus
```

**That's just renaming routes + reorganizing controllers. Everything else is already there.**

---

## IMPLEMENTATION: ONE ROUTE FILE

Instead of building 3 separate apps, just add one route file to MagicAI:

```php
// routes/chatbot.php (new)

Route::middleware(['web', 'auth', 'workspace.active'])
    ->group(function() {
        
        // =====================
        // OWNER DASHBOARD (Titan Command)
        // =====================
        Route::middleware('can:manage-chatbots')
            ->prefix('dashboard/chatbots')
            ->name('dashboard.chatbots.')
            ->group(function() {
                Route::controller(ChatbotCommandController::class)->group(function() {
                    Route::get('', 'index')->name('index');
                    Route::post('', 'store')->name('store');
                    Route::get('{chatbot}', 'show')->name('show');
                    Route::put('{chatbot}', 'update')->name('update');
                    Route::delete('{chatbot}', 'destroy')->name('destroy');
                    Route::get('{chatbot}/analytics', 'analytics')->name('analytics');
                    Route::get('{chatbot}/conversations', 'conversations')->name('conversations');
                });
            });
        
        // =====================
        // AGENT PANEL (Titan Go)
        // =====================
        Route::middleware('can:respond-to-conversations')
            ->prefix('dashboard/agent')
            ->name('dashboard.agent.')
            ->group(function() {
                Route::controller(ChatbotAgentController::class)->group(function() {
                    Route::get('', 'index')->name('index');
                    Route::get('conversations', 'conversations')->name('conversations');
                    Route::get('conversations/{conversation}', 'show')->name('show');
                    Route::post('conversations/{conversation}/reply', 'reply')->name('reply');
                    Route::post('conversations/{conversation}/transfer', 'transfer')->name('transfer');
                });
            });
        
        // =====================
        // CUSTOMER PORTAL (Titan Nexus)
        // =====================
        Route::prefix('customer-portal')
            ->name('customer.portal.')
            ->group(function() {
                Route::controller(ChatbotCustomerController::class)->group(function() {
                    Route::get('', 'index')->name('index');
                    Route::get('conversations/{conversation}', 'show')->name('show');
                    Route::post('conversations/{conversation}/message', 'storeMessage')->name('message.store');
                });
            });
    });

// Public webhooks (no auth, rate-limited)
Route::middleware('throttle:chatbot-webhook')
    ->prefix('api/v1/chatbot-webhook')
    ->group(function() {
        Route::post('{chatbot}/channel/{channel}', [ChatbotWebhookController::class, 'handle'])
            ->name('api.chatbot.webhook');
    });
```

**That's the entire "Titan Command/Go/Nexus" infrastructure.** It's just routes + controllers + views.

---

## STEP-BY-STEP: WHAT TO ADD TO MAGICAI

### 1. Controllers (3 new files)

```php
// app/Http/Controllers/Dashboard/ChatbotCommandController.php
class ChatbotCommandController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Chatbot::class, 'chatbot');
    }
    
    public function index()
    {
        // List all chatbots in user's workspace
        $chatbots = Chatbot::where('workspace_id', auth()->user()->workspace_id)
            ->with('conversations', 'channels')
            ->latest()
            ->paginate();
        
        return view('chatbot.command.index', ['chatbots' => $chatbots]);
    }
    
    public function store(StoreChatbotRequest $request)
    {
        // Create chatbot (owned by workspace, not individual user)
        $chatbot = Chatbot::create([
            'workspace_id' => auth()->user()->workspace_id,
            'user_id' => auth()->id(),  // Creator
            'name' => $request->name,
            'ai_model' => $request->ai_model ?? 'claude-sonnet',
            'interaction_type' => 'SMART_SWITCH',
        ]);
        
        // Initialize channels
        foreach ($request->enable_channels ?? ['telegram', 'whatsapp'] as $channel) {
            ChatbotChannel::create([
                'chatbot_id' => $chatbot->id,
                'channel_type' => $channel,
                'is_active' => true,
            ]);
        }
        
        return redirect()->route('dashboard.chatbots.show', $chatbot)
            ->with('success', 'Chatbot created');
    }
    
    public function show(Chatbot $chatbot)
    {
        return view('chatbot.command.show', [
            'chatbot' => $chatbot->load('conversations', 'channels', 'knowledgeBases'),
            'conversations' => ChatbotConversation::where('chatbot_id', $chatbot->id)
                ->latest('last_activity_at')
                ->paginate(20),
            'analytics' => app(ChatbotAnalyticsService::class)->getMetrics($chatbot),
        ]);
    }
    
    public function update(UpdateChatbotRequest $request, Chatbot $chatbot)
    {
        $chatbot->update($request->validated());
        return back()->with('success', 'Chatbot updated');
    }
    
    public function destroy(Chatbot $chatbot)
    {
        $chatbot->delete();
        return redirect()->route('dashboard.chatbots.index')
            ->with('success', 'Chatbot deleted');
    }
}

// app/Http/Controllers/Dashboard/ChatbotAgentController.php
class ChatbotAgentController extends Controller
{
    public function index()
    {
        // Agent's active conversations
        $conversations = ChatbotConversation::where('assigned_agent_id', auth()->id())
            ->where('closed', false)
            ->with('chatbot', 'customer', 'assignedAgent')
            ->latest('last_activity_at')
            ->paginate();
        
        return view('chatbot.agent.index', [
            'conversations' => $conversations,
            'unread_count' => $this->getUnreadCount(),
        ]);
    }
    
    public function show(ChatbotConversation $conversation)
    {
        $this->authorize('respond-as', $conversation); // Policy check
        
        $messages = ChatbotHistory::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->paginate(50);
        
        return view('chatbot.agent.show', [
            'conversation' => $conversation,
            'messages' => $messages,
            'customer' => $conversation->customer,
        ]);
    }
    
    public function reply(Request $request, ChatbotConversation $conversation)
    {
        $this->authorize('respond-as', $conversation);
        
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);
        
        // Log message
        $history = ChatbotHistory::create([
            'conversation_id' => $conversation->id,
            'message' => $validated['message'],
            'role' => 'assistant',
            'model' => 'human-agent',
        ]);
        
        // Send via channel
        $this->sendViaChannel($conversation, $validated['message']);
        
        // Update timestamp
        $conversation->update(['last_activity_at' => now()]);
        
        // Broadcast to customer (Ably)
        broadcast(new MessageSent($conversation, $history));
        
        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        return back()->with('success', 'Message sent');
    }
}

// app/Http/Controllers/Portal/ChatbotCustomerController.php
class ChatbotCustomerController extends Controller
{
    public function index()
    {
        // Customer's conversations (across all chatbots/channels)
        $conversations = ChatbotConversation::where('chatbot_customer_id', auth()->id())
            ->with('chatbot', 'assignedAgent')
            ->latest('last_activity_at')
            ->paginate();
        
        return view('chatbot.portal.index', [
            'conversations' => $conversations,
        ]);
    }
    
    public function show(ChatbotConversation $conversation)
    {
        // Customer can only view own conversations
        $this->authorize('view', $conversation);
        
        $messages = ChatbotHistory::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->paginate(50);
        
        return view('chatbot.portal.show', [
            'conversation' => $conversation,
            'messages' => $messages,
            'agent' => $conversation->assignedAgent,
        ]);
    }
    
    public function storeMessage(Request $request, ChatbotConversation $conversation)
    {
        $this->authorize('view', $conversation);
        
        if ($conversation->closed) {
            return back()->withErrors('Conversation is closed');
        }
        
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);
        
        // Log customer message
        ChatbotHistory::create([
            'conversation_id' => $conversation->id,
            'message' => $validated['message'],
            'role' => 'user',
        ]);
        
        // Update activity
        $conversation->update(['last_activity_at' => now()]);
        
        // Generate response or route to agent
        if ($conversation->assigned_agent_id) {
            // Notify agent
            broadcast(new CustomerMessageReceived($conversation));
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
        
        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        return back();
    }
}
```

### 2. Policies (1 new file)

```php
// app/Policies/ChatbotPolicy.php
class ChatbotPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(User $user): bool
    {
        return $user->can('view-chatbots');
    }
    
    public function view(User $user, Chatbot $chatbot): bool
    {
        return $user->workspace_id === $chatbot->workspace_id
            && $user->can('view-chatbots');
    }
    
    public function create(User $user): bool
    {
        return $user->can('create-chatbots');
    }
    
    public function update(User $user, Chatbot $chatbot): bool
    {
        return $user->workspace_id === $chatbot->workspace_id
            && $user->can('manage-chatbots');
    }
    
    public function delete(User $user, Chatbot $chatbot): bool
    {
        return $user->workspace_id === $chatbot->workspace_id
            && $user->can('manage-chatbots');
    }
}

// app/Policies/ChatbotConversationPolicy.php
class ChatbotConversationPolicy
{
    public function respondAs(User $user, ChatbotConversation $conversation): bool
    {
        // Agent can reply if assigned to this conversation or has admin permission
        return $conversation->assigned_agent_id === $user->id
            || $user->can('manage-all-conversations');
    }
    
    public function view(User $user, ChatbotConversation $conversation): bool
    {
        // Customer views own conversations
        // Agent views assigned conversations
        return $user->id === $conversation->chatbot_customer_id
            || $user->id === $conversation->assigned_agent_id
            || $user->can('manage-all-conversations');
    }
}
```

### 3. Views (Blade templates)

```php
// resources/views/chatbot/command/index.blade.php
@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-6">My Chatbots</h1>
    
    <a href="{{ route('dashboard.chatbots.create') }}" class="btn btn-primary mb-6">
        + Create New Chatbot
    </a>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($chatbots as $chatbot)
            <div class="card shadow-md">
                <div class="card-body">
                    <h2 class="card-title">{{ $chatbot->name }}</h2>
                    <p class="text-sm text-gray-600">{{ $chatbot->description }}</p>
                    
                    <div class="mt-4 text-sm">
                        <p><strong>Conversations:</strong> {{ $chatbot->conversations->count() }}</p>
                        <p><strong>Channels:</strong> {{ $chatbot->channels->count() }}</p>
                    </div>
                    
                    <div class="card-actions mt-6">
                        <a href="{{ route('dashboard.chatbots.show', $chatbot) }}" class="btn btn-sm">
                            View
                        </a>
                        <a href="{{ route('dashboard.chatbots.edit', $chatbot) }}" class="btn btn-sm">
                            Edit
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <p class="col-span-full text-center text-gray-500">
                No chatbots yet. <a href="{{ route('dashboard.chatbots.create') }}" class="link">Create one</a>
            </p>
        @endforelse
    </div>
    
    {{ $chatbots->links() }}
</div>
@endsection

// resources/views/chatbot/agent/index.blade.php
@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-6">My Conversations</h1>
    
    @if($unread_count > 0)
        <div class="alert alert-info mb-6">
            You have {{ $unread_count }} new message{{ $unread_count > 1 ? 's' : '' }}
        </div>
    @endif
    
    <div class="space-y-4">
        @forelse($conversations as $conversation)
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold">{{ $conversation->chatbot->name }}</h3>
                            <p class="text-sm text-gray-600">
                                @if($conversation->customer)
                                    {{ $conversation->customer->channel_identifier }}
                                @else
                                    Unknown customer
                                @endif
                            </p>
                            <p class="text-xs text-gray-500">
                                Last activity: {{ $conversation->last_activity_at->diffForHumans() }}
                            </p>
                        </div>
                        <a href="{{ route('dashboard.agent.show', $conversation) }}" class="btn btn-sm btn-primary">
                            Reply
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-500">No active conversations</p>
        @endforelse
    </div>
    
    {{ $conversations->links() }}
</div>
@endsection

// resources/views/chatbot/portal/index.blade.php
@extends('layouts.customer')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-6">My Conversations</h1>
    
    <div class="space-y-4">
        @forelse($conversations as $conversation)
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold">{{ $conversation->chatbot->name }}</h3>
                            <p class="text-xs text-gray-500">
                                Started: {{ $conversation->created_at->format('M d, Y') }}
                            </p>
                            <p class="text-xs">
                                Status: 
                                <span class="badge {{ $conversation->closed ? 'badge-error' : 'badge-success' }}">
                                    {{ $conversation->closed ? 'Closed' : 'Open' }}
                                </span>
                            </p>
                        </div>
                        <a href="{{ route('customer.portal.show', $conversation) }}" class="btn btn-sm btn-primary">
                            View
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-500">No conversations yet</p>
        @endforelse
    </div>
</div>
@endsection
```

### 4. Register Routes in MagicAI

```php
// app/Providers/RouteServiceProvider.php (existing file, add to boot method)

public function boot(): void
{
    $this->routes(function () {
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));

        Route::middleware('web')
            ->group(base_path('routes/web.php'));
        
        Route::middleware('web')
            ->group(base_path('routes/chatbot.php')); // Add this
    });
}
```

### 5. Register Policies

```php
// app/Providers/AuthServiceProvider.php (add to boot method)

use App\Models\Chatbot;
use App\Policies\ChatbotPolicy;
use App\Models\ChatbotConversation;
use App\Policies\ChatbotConversationPolicy;

public function boot(): void
{
    $this->registerPolicies();
    
    Gate::policy(Chatbot::class, ChatbotPolicy::class);
    Gate::policy(ChatbotConversation::class, ChatbotConversationPolicy::class);
}
```

---

## DATABASE: ALREADY UNIFIED

You don't need to change anything.

MagicAI's existing database structure already supports this:

```sql
-- WorkSuite tables (already exist)
CREATE TABLE workspaces (
    id BIGINT PRIMARY KEY,
    name VARCHAR,
    -- ...
);

CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    workspace_id BIGINT,
    email VARCHAR,
    role ENUM('owner', 'staff', 'agent', 'customer'),
    -- ...
);

-- Chatbot extension tables (already exist, same database)
CREATE TABLE ext_chatbots (
    id BIGINT PRIMARY KEY,
    workspace_id BIGINT,      -- Tied to workspace
    user_id BIGINT,           -- Creator
    name VARCHAR,
    ai_model VARCHAR,
    -- ...
);

CREATE TABLE ext_chatbot_conversations (
    id BIGINT PRIMARY KEY,
    chatbot_id BIGINT,
    chatbot_customer_id BIGINT,      -- Chatbot's customer ID
    assigned_agent_id BIGINT,         -- WorkSuite user (agent) ← NEW
    service_request_id BIGINT,        -- Link to WorkSuite (optional)
    closed BOOLEAN,
    -- ...
);
```

**Add just two columns:**

```sql
ALTER TABLE ext_chatbots 
    ADD COLUMN workspace_id BIGINT REFERENCES workspaces(id);

ALTER TABLE ext_chatbot_conversations 
    ADD COLUMN assigned_agent_id BIGINT REFERENCES users(id),
    ADD COLUMN service_request_id BIGINT REFERENCES service_requests(id);
```

That's it.

---

## MIGRATION: ONE SIMPLE MIGRATION

```php
// database/migrations/2024_03_24_add_workspace_to_chatbots.php

class AddWorkspaceToChatbots extends Migration
{
    public function up(): void
    {
        Schema::table('ext_chatbots', function (Blueprint $table) {
            $table->foreignId('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();
            $table->index('workspace_id');
        });
        
        Schema::table('ext_chatbot_conversations', function (Blueprint $table) {
            $table->foreignId('assigned_agent_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            
            $table->foreignId('service_request_id')
                ->nullable()
                ->constrained('service_requests')
                ->nullOnDelete();
            
            $table->index(['assigned_agent_id', 'closed']);
        });
    }
    
    public function down(): void
    {
        Schema::table('ext_chatbots', function (Blueprint $table) {
            $table->dropForeignKeyConstraints();
            $table->dropColumn('workspace_id');
        });
        
        Schema::table('ext_chatbot_conversations', function (Blueprint $table) {
            $table->dropForeignKeyConstraints();
            $table->dropColumn(['assigned_agent_id', 'service_request_id']);
        });
    }
}
```

Run: `php artisan migrate`

Done.

---

## SUMMARY: IT'S ALREADY UNIFIED

**You're not "integrating" anything.**

The chatbot extension **already runs on MagicAI's database.**

All you're doing is:

1. ✅ Adding `workspace_id` column (one migration)
2. ✅ Adding `assigned_agent_id` column (same migration)
3. ✅ Creating 3 controllers (ChatbotCommand, ChatbotAgent, ChatbotCustomer)
4. ✅ Creating 2 policies (ChatbotPolicy, ChatbotConversationPolicy)
5. ✅ Adding 1 route file (routes/chatbot.php)
6. ✅ Creating 3 blade views (command/agent/customer dashboards)

**That's the entire "Titan Command/Go/Nexus" implementation.**

Everything else is already there:
- ✅ Database (single instance)
- ✅ Authentication (WorkSuite auth)
- ✅ Models (Chatbot, ChatbotConversation, etc)
- ✅ Services (GeneratorService, ChatbotService, etc)
- ✅ Webhook handlers (for channels)

---

## WHAT YOU BUILD

```
MagicAI
├─ Already has ChatBot extension
├─ Already uses one database
├─ Already has auth
│
├─ Add: routes/chatbot.php
├─ Add: ChatbotCommandController
├─ Add: ChatbotAgentController
├─ Add: ChatbotCustomerController
├─ Add: ChatbotPolicy
├─ Add: ChatbotConversationPolicy
├─ Add: Views for each dashboard
└─ Run: php artisan migrate

Result: Three different dashboards, one app, one database, one auth system.
```

**That's literally it.**

---

## IMPLEMENTATION TIMELINE (REALISTIC)

```
Day 1:
  □ Add workspace_id and assigned_agent_id columns (migration)
  □ Write ChatbotPolicy and ChatbotConversationPolicy

Day 2:
  □ Create routes/chatbot.php
  □ Create ChatbotCommandController
  □ Create ChatbotAgentController
  □ Create ChatbotCustomerController

Day 3:
  □ Create blade views for each dashboard
  □ Test owner dashboard (create/view/edit chatbots)
  □ Test agent dashboard (view/reply to conversations)
  □ Test customer portal (view conversations)

Day 4:
  □ Polish UX
  □ Add breadcrumbs, navigation
  □ Add notifications (Ably integration)
  □ Test on mobile

Day 5:
  □ Final testing
  □ Documentation
  □ Deploy
```

**5 days to ship all three tiers.**

Compare that to 8 weeks for separate apps.

---

## KEY INSIGHT

**You were overthinking it.**

The architecture you're asking about doesn't require any new thinking.

The chatbot extension **is already part of MagicAI.**

It **already runs off the MagicAI database.**

All you're doing is **exposing different views for different user roles.**

That's not "integration." That's just **good application design.**

Everything is already unified.

You just need to:
1. Add scoping columns
2. Add role-based routing
3. Create dashboards for each role

Done. ✨
