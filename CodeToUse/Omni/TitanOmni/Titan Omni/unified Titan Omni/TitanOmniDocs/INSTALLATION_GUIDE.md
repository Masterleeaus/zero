# ChatBot Command/Agent/Customer Controllers - Installation Guide

## Overview

This guide walks you through integrating the three chatbot controllers (Command, Agent, Customer) into your MagicAI application.

**Files to Copy:**
- `ChatbotCommandController.php` → `app/Http/Controllers/Dashboard/`
- `ChatbotAgentController.php` → `app/Http/Controllers/Dashboard/`
- `ChatbotCustomerController.php` → `app/Http/Controllers/Portal/`
- `ChatbotPolicy.php` → `app/Policies/`
- `ChatbotConversationPolicy.php` → `app/Policies/`
- `Requests-chatbot.php` → `app/Http/Requests/` (split into individual files)
- `routes-chatbot.php` → `routes/chatbot.php`
- `migration-chatbot-scoping.php` → `database/migrations/`

---

## Step 1: Copy Controllers

### Command Controller
```bash
cp ChatbotCommandController.php app/Http/Controllers/Dashboard/ChatbotCommandController.php
```

### Agent Controller
```bash
cp ChatbotAgentController.php app/Http/Controllers/Dashboard/ChatbotAgentController.php
```

### Customer Controller
```bash
mkdir -p app/Http/Controllers/Portal
cp ChatbotCustomerController.php app/Http/Controllers/Portal/ChatbotCustomerController.php
```

---

## Step 2: Copy Policies

```bash
cp ChatbotPolicy.php app/Policies/ChatbotPolicy.php
cp ChatbotConversationPolicy.php app/Policies/ChatbotConversationPolicy.php
```

---

## Step 3: Create Request Classes

Split `Requests-chatbot.php` into individual files in `app/Http/Requests/`:

```bash
# Create each request class
mkdir -p app/Http/Requests/Chatbot

# Then copy each class from Requests-chatbot.php into:
app/Http/Requests/Chatbot/StoreChatbotRequest.php
app/Http/Requests/Chatbot/UpdateChatbotRequest.php
app/Http/Requests/Chatbot/StoreConversationMessageRequest.php
app/Http/Requests/Chatbot/TransferConversationRequest.php
app/Http/Requests/Chatbot/CloseConversationRequest.php
app/Http/Requests/Chatbot/RateFeedbackRequest.php
```

Or keep namespace as:
```php
namespace App\Http\Requests;
```

Then use in controllers as:
```php
use App\Http\Requests\StoreChatbotRequest;
use App\Http\Requests\UpdateChatbotRequest;
```

---

## Step 4: Copy Routes File

```bash
cp routes-chatbot.php routes/chatbot.php
```

### Register Routes in RouteServiceProvider

Edit `app/Providers/RouteServiceProvider.php` and add to the `boot()` method:

```php
public function boot(): void
{
    $this->routes(function () {
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));

        Route::middleware('web')
            ->group(base_path('routes/web.php'));
        
        // Add this line:
        Route::middleware('web')
            ->group(base_path('routes/chatbot.php'));
    });
}
```

---

## Step 5: Register Policies

Edit `app/Providers/AuthServiceProvider.php`:

```php
namespace App\Providers;

use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Policies\ChatbotPolicy;
use App\Policies\ChatbotConversationPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // ... existing policies
        Chatbot::class => ChatbotPolicy::class,
        ChatbotConversation::class => ChatbotConversationPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
        
        // If using Gate instead:
        // Gate::policy(Chatbot::class, ChatbotPolicy::class);
        // Gate::policy(ChatbotConversation::class, ChatbotConversationPolicy::class);
    }
}
```

---

## Step 6: Run Database Migration

```bash
# Copy migration file
cp migration-chatbot-scoping.php database/migrations/2024_03_24_000000_add_workspace_to_chatbots.php

# Update the timestamp in the filename to current time:
# Example: 2024_03_24_103045_add_workspace_to_chatbots.php

# Run migration
php artisan migrate
```

---

## Step 7: Verify Installation

### Check Routes
```bash
php artisan route:list | grep chatbot
```

Expected output should show:
```
dashboard/chatbots                          GET|HEAD      Dashboard\ChatbotCommandController@index
dashboard/chatbots/create                   GET|HEAD      Dashboard\ChatbotCommandController@create
dashboard/chatbots                          POST          Dashboard\ChatbotCommandController@store
dashboard/chatbots/{chatbot}                GET|HEAD      Dashboard\ChatbotCommandController@show
dashboard/chatbots/{chatbot}/edit           GET|HEAD      Dashboard\ChatbotCommandController@edit
dashboard/chatbots/{chatbot}                PUT           Dashboard\ChatbotCommandController@update
dashboard/chatbots/{chatbot}                DELETE        Dashboard\ChatbotCommandController@destroy
dashboard/agent                             GET|HEAD      Dashboard\ChatbotAgentController@index
dashboard/agent/conversations/{conversation} GET|HEAD      Dashboard\ChatbotAgentController@show
dashboard/agent/conversations/{conversation}/reply POST  Dashboard\ChatbotAgentController@reply
dashboard/agent/conversations/{conversation}/transfer POST Dashboard\ChatbotAgentController@transfer
portal/conversations                        GET|HEAD      Portal\ChatbotCustomerController@index
portal/conversations                        POST          Portal\ChatbotCustomerController@create
portal/conversations/{conversation}         GET|HEAD      Portal\ChatbotCustomerController@show
portal/conversations/{conversation}/message POST          Portal\ChatbotCustomerController@sendMessage
```

### Check Policies
```bash
php artisan policy:list | grep Chatbot
```

Should show both ChatbotPolicy and ChatbotConversationPolicy.

---

## Step 8: Create Views (Blade Templates)

Create directory structure:
```
resources/views/
├── dashboard/
│   └── chatbots/
│       ├── index.blade.php
│       ├── create.blade.php
│       ├── show.blade.php
│       └── edit.blade.php
├── portal/
│   └── conversations/
│       ├── index.blade.php
│       └── show.blade.php
└── agent/
    ├── index.blade.php
    └── show.blade.php
```

### Basic View Examples

#### resources/views/dashboard/chatbots/index.blade.php
```blade
@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">My Chatbots</h1>
        <a href="{{ route('dashboard.chatbots.create') }}" class="btn btn-primary">
            + Create New Chatbot
        </a>
    </div>

    {{-- Display chatbots --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($chatbots as $chatbot)
            <div class="card shadow-md">
                <div class="card-body">
                    <h2 class="card-title text-lg">{{ $chatbot->name }}</h2>
                    <p class="text-sm text-gray-600">{{ $chatbot->description }}</p>
                    
                    <div class="mt-4 text-sm space-y-1">
                        <p><strong>Model:</strong> {{ $chatbot->ai_model }}</p>
                        <p><strong>Conversations:</strong> {{ $chatbot->conversations_count }}</p>
                        <p><strong>Open:</strong> {{ $chatbot->open_conversations_count }}</p>
                    </div>
                    
                    <div class="card-actions mt-6 justify-end gap-2">
                        <a href="{{ route('dashboard.chatbots.show', $chatbot) }}" class="btn btn-sm btn-outline">
                            View
                        </a>
                        <a href="{{ route('dashboard.chatbots.edit', $chatbot) }}" class="btn btn-sm btn-outline">
                            Edit
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500 mb-4">No chatbots yet</p>
                <a href="{{ route('dashboard.chatbots.create') }}" class="btn btn-primary">
                    Create Your First Chatbot
                </a>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-8">
        {{ $chatbots->links() }}
    </div>
</div>
@endsection
```

#### resources/views/portal/conversations/index.blade.php
```blade
@extends('layouts.customer')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-6">My Conversations</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="stat bg-base-100 shadow">
            <div class="stat-title">Total</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow">
            <div class="stat-title">Open</div>
            <div class="stat-value">{{ $stats['open'] }}</div>
        </div>
        <div class="stat bg-base-100 shadow">
            <div class="stat-title">Closed</div>
            <div class="stat-value">{{ $stats['closed'] }}</div>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($conversations as $conversation)
            <div class="card bg-base-100 shadow-sm border">
                <div class="card-body">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="card-title text-lg">{{ $conversation->chatbot->name }}</h3>
                            <p class="text-sm text-gray-600">
                                Started {{ $conversation->created_at->diffForHumans() }}
                            </p>
                            @if($conversation->assignedAgent)
                                <p class="text-sm text-blue-600 font-medium">
                                    Assigned to: {{ $conversation->assignedAgent->name }}
                                </p>
                            @endif
                            <div class="mt-2">
                                <span class="badge {{ $conversation->closed ? 'badge-error' : 'badge-success' }}">
                                    {{ $conversation->closed ? 'Closed' : 'Open' }}
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('portal.conversations.show', $conversation) }}" class="btn btn-primary btn-sm">
                            View
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info">
                <svg class="stroke-current shrink-0 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>No conversations yet. Start a chat to get help!</span>
            </div>
        @endforelse
    </div>

    {{ $conversations->links() }}
</div>
@endsection
```

---

## Step 9: Create Middleware (If Needed)

If you need to ensure workspace scoping, you may need a middleware:

```bash
php artisan make:middleware WorkspaceScopedMiddleware
```

Edit `app/Http/Middleware/WorkspaceScopedMiddleware.php`:

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WorkspaceScopedMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Verify user's workspace is active/valid
        if (!auth()->user() || !auth()->user()->workspace) {
            return redirect('/');
        }

        return $next($request);
    }
}
```

Register in `app/Http/Kernel.php`:
```php
protected $routeMiddleware = [
    // ...
    'workspace.active' => \App\Http\Middleware\WorkspaceScopedMiddleware::class,
];
```

---

## Step 10: Add Permissions (Optional)

If your app uses a permissions system, add these permissions:

```php
// Seed these into your permissions table
Permission::firstOrCreate(['name' => 'view-chatbots']);
Permission::firstOrCreate(['name' => 'create-chatbots']);
Permission::firstOrCreate(['name' => 'manage-chatbots']);
Permission::firstOrCreate(['name' => 'respond-to-conversations']);
Permission::firstOrCreate(['name' => 'manage-all-conversations']);
```

Assign to roles:
```php
// Owner role
Role::where('name', 'owner')
    ->first()
    ->permissions()
    ->attach(Permission::whereIn('name', [
        'view-chatbots',
        'create-chatbots',
        'manage-chatbots',
        'manage-all-conversations',
    ])->pluck('id'));

// Agent role
Role::where('name', 'agent')
    ->first()
    ->permissions()
    ->attach(Permission::whereIn('name', [
        'respond-to-conversations',
    ])->pluck('id'));

// Customer role
Role::where('name', 'customer')
    ->first()
    ->permissions()
    ->attach(Permission::whereIn('name', [
        // Customers don't need explicit permissions
        // They access via policy
    ])->pluck('id'));
```

---

## Step 11: Test the Installation

### Test Routes
```bash
# List all chatbot routes
php artisan route:list --name=chatbot

# Check specific route
php artisan route:show dashboard.chatbots.index
```

### Test Controllers
```bash
# Try accessing the dashboard
# http://yourapp.local/dashboard/chatbots

# Try accessing agent panel
# http://yourapp.local/dashboard/agent

# Try accessing customer portal
# http://yourapp.local/portal/conversations
```

### Test Database
```bash
# Check migration ran successfully
php artisan migrate:status | grep chatbot

# Verify columns were added
php artisan tinker
>>> Schema::getColumns('ext_chatbots')
>>> Schema::getColumns('ext_chatbot_conversations')
```

---

## Step 12: Create Events (Optional but Recommended)

For real-time features, create Broadcastable events:

```bash
php artisan make:event ChatbotConversationAssigned
php artisan make:event AgentMessageSent
php artisan make:event CustomerMessageReceived
php artisan make:event MessageSent
```

Each should implement `ShouldBroadcast` or `ShouldBroadcastNow`.

---

## Troubleshooting

### Routes Not Working
```bash
# Clear route cache
php artisan route:clear

# Verify RouteServiceProvider includes chatbot.php
grep "chatbot.php" app/Providers/RouteServiceProvider.php
```

### Policies Not Working
```bash
# Clear authorization cache
php artisan cache:clear

# Verify policies are registered in AuthServiceProvider
php artisan tinker
>>> Gate::getPolicies()
```

### Migration Issues
```bash
# Check migration status
php artisan migrate:status

# If stuck, rollback and retry
php artisan migrate:rollback
php artisan migrate

# Check for SQL errors
php artisan tinker
>>> Schema::hasColumn('ext_chatbots', 'workspace_id')
>>> Schema::hasColumn('ext_chatbot_conversations', 'assigned_agent_id')
```

### Missing Request Classes
```bash
# If getting "class not found" errors, ensure requests are in correct namespace:
app/Http/Requests/StoreChatbotRequest.php
app/Http/Requests/UpdateChatbotRequest.php
# etc...

# Or if in subdirectory:
app/Http/Requests/Chatbot/StoreChatbotRequest.php
# Update namespace accordingly
```

---

## Next Steps

1. **Create Blade templates** for each view
2. **Add notifications** for real-time features (Ably)
3. **Add analytics** dashboard showing metrics
4. **Add webhooks** configuration UI for channels
5. **Add knowledge base** management interface

---

## File Checklist

- [ ] ChatbotCommandController.php copied
- [ ] ChatbotAgentController.php copied
- [ ] ChatbotCustomerController.php copied
- [ ] ChatbotPolicy.php copied
- [ ] ChatbotConversationPolicy.php copied
- [ ] Request classes created
- [ ] routes/chatbot.php created
- [ ] RouteServiceProvider updated
- [ ] AuthServiceProvider updated (policies)
- [ ] Migration copied and run
- [ ] Blade views created
- [ ] Routes verified with `php artisan route:list`
- [ ] Policies verified with `php artisan policy:list`
- [ ] Database columns verified

---

**Done!** Your ChatBot Command/Agent/Customer system is installed and ready to use. 🚀
