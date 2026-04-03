# Quick Reference Card - Titan Command/Go/Nexus Setup

## 📋 One-Page Checklist

### Pre-Setup Verification
```bash
# Check User has workspace
php artisan tinker
>>> User::first()->workspace_id   # Should not be null
>>> exit

# Check ChatBot tables exist
php artisan migrate:status | grep chatbot
```

### File Setup (Copy These)
- [ ] `ChatbotCommandController.php` → `app/Http/Controllers/Dashboard/`
- [ ] `ChatbotAgentController.php` → `app/Http/Controllers/Dashboard/`
- [ ] `ChatbotCustomerController.php` → `app/Http/Controllers/Portal/`
- [ ] `ChatbotPolicy.php` → `app/Policies/`
- [ ] `ChatbotConversationPolicy.php` → `app/Policies/`
- [ ] `routes-chatbot.php` → `routes/chatbot.php`
- [ ] `migration-chatbot-scoping.php` → `database/migrations/`
- [ ] Create 6 Request classes in `app/Http/Requests/`

### Provider Updates (Edit These)
- [ ] `app/Providers/RouteServiceProvider.php` - Add routes/chatbot.php line
- [ ] `app/Providers/AuthServiceProvider.php` - Add policy registrations

### Database
- [ ] `php artisan migrate`

### Views (Create These)
- [ ] `resources/views/dashboard/chatbots/index.blade.php`
- [ ] `resources/views/dashboard/agent/index.blade.php`
- [ ] `resources/views/portal/conversations/index.blade.php`

### Verification
- [ ] `php artisan route:list | grep chatbot`
- [ ] `php artisan policy:list | grep Chatbot`
- [ ] Visit http://localhost:8000/dashboard/chatbots

---

## 🔧 Essential Commands

```bash
# Create directories
mkdir -p app/Http/Controllers/Portal
mkdir -p app/Policies

# Run migration
php artisan migrate

# Clear caches
php artisan route:clear
php artisan cache:clear
php artisan config:cache

# Verify installation
php artisan route:list --name=chatbot
php artisan policy:list | grep Chatbot

# Test database
php artisan tinker
>>> Schema::getColumns('ext_chatbots')
>>> Schema::getColumns('ext_chatbot_conversations')

# Test routes
php artisan route:show dashboard.chatbots.index

# Tinker testing
php artisan tinker
>>> \App\Models\Workspace::first()
>>> \App\Extensions\Chatbot\System\Models\Chatbot::first()
>>> auth()->user()->workspace
>>> auth()->user()->can('manage-chatbots')
```

---

## 📝 Request Classes to Create

### 1. StoreChatbotRequest.php
```php
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreChatbotRequest extends FormRequest {
    public function authorize(): bool { 
        return $this->user()->can('create-chatbots'); 
    }
    public function rules(): array {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:ext_chatbots,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'ai_model' => ['required', 'string', 'in:claude-opus,claude-sonnet,claude-haiku,gpt-4,gpt-3.5-turbo'],
            'channels' => ['sometimes', 'array'],
            'channels.*' => ['string', 'in:telegram,whatsapp,messenger,voice,external'],
        ];
    }
}
```

### 2. UpdateChatbotRequest.php
```php
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateChatbotRequest extends FormRequest {
    public function authorize(): bool { 
        return $this->user()->can('manage-chatbots'); 
    }
    public function rules(): array {
        $id = $this->route('chatbot')->id;
        return [
            'name' => ['sometimes', 'string', 'max:255', "unique:ext_chatbots,name,$id"],
            'description' => ['sometimes', 'string', 'max:1000'],
            'ai_model' => ['sometimes', 'string', 'in:claude-opus,claude-sonnet,claude-haiku,gpt-4,gpt-3.5-turbo'],
        ];
    }
}
```

### 3. StoreConversationMessageRequest.php
```php
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreConversationMessageRequest extends FormRequest {
    public function authorize(): bool { 
        return $this->user()->can('view', $this->route('conversation')); 
    }
    public function rules(): array {
        return [
            'message' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:50000'],
        ];
    }
}
```

### 4. TransferConversationRequest.php
```php
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class TransferConversationRequest extends FormRequest {
    public function authorize(): bool { 
        return $this->user()->can('transfer', $this->route('conversation')); 
    }
    public function rules(): array {
        return [
            'agent_id' => ['required', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
```

### 5. CloseConversationRequest.php
```php
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class CloseConversationRequest extends FormRequest {
    public function authorize(): bool { 
        return $this->user()->can('close', $this->route('conversation')); 
    }
    public function rules(): array {
        return [
            'resolution_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
```

### 6. RateFeedbackRequest.php
```php
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class RateFeedbackRequest extends FormRequest {
    public function authorize(): bool { 
        return $this->user()->can('rate', $this->route('conversation')); 
    }
    public function rules(): array {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'feedback' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
```

---

## 🔐 Provider Updates

### RouteServiceProvider.php (Add to boot method)
```php
// Around line where routes are registered:
Route::middleware('web')->group(base_path('routes/chatbot.php'));
```

### AuthServiceProvider.php (Add these imports and property)
```php
use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Policies\ChatbotPolicy;
use App\Policies\ChatbotConversationPolicy;

protected $policies = [
    Chatbot::class => ChatbotPolicy::class,
    ChatbotConversation::class => ChatbotConversationPolicy::class,
];
```

---

## 📊 Database Columns Added

```sql
-- ext_chatbots table
ALTER TABLE ext_chatbots ADD COLUMN workspace_id BIGINT UNSIGNED NOT NULL;
ALTER TABLE ext_chatbots ADD INDEX idx_workspace (workspace_id);

-- ext_chatbot_conversations table
ALTER TABLE ext_chatbot_conversations ADD COLUMN assigned_agent_id BIGINT UNSIGNED NULLABLE;
ALTER TABLE ext_chatbot_conversations ADD COLUMN service_request_id BIGINT UNSIGNED NULLABLE;
ALTER TABLE ext_chatbot_conversations ADD COLUMN customer_read_at TIMESTAMP NULLABLE;
ALTER TABLE ext_chatbot_conversations ADD COLUMN closed_at TIMESTAMP NULLABLE;

-- ext_chatbot_histories table
ALTER TABLE ext_chatbot_histories ADD COLUMN customer_read_at TIMESTAMP NULLABLE;
```

---

## 🌐 Routes Added

| Route | Method | Controller | Purpose |
|-------|--------|-----------|---------|
| `/dashboard/chatbots` | GET | CommandController@index | List chatbots |
| `/dashboard/chatbots/create` | GET | CommandController@create | Create form |
| `/dashboard/chatbots` | POST | CommandController@store | Store chatbot |
| `/dashboard/chatbots/{id}` | GET | CommandController@show | View chatbot |
| `/dashboard/chatbots/{id}/edit` | GET | CommandController@edit | Edit form |
| `/dashboard/chatbots/{id}` | PUT | CommandController@update | Update chatbot |
| `/dashboard/chatbots/{id}` | DELETE | CommandController@destroy | Delete chatbot |
| `/dashboard/agent` | GET | AgentController@index | Agent conversations |
| `/dashboard/agent/conversations/{id}` | GET | AgentController@show | View conversation |
| `/dashboard/agent/conversations/{id}/reply` | POST | AgentController@reply | Send reply |
| `/dashboard/agent/conversations/{id}/transfer` | POST | AgentController@transfer | Transfer conversation |
| `/dashboard/agent/conversations/{id}/close` | POST | AgentController@close | Close conversation |
| `/portal/conversations` | GET | CustomerController@index | Customer conversations |
| `/portal/conversations/{id}` | GET | CustomerController@show | View conversation |
| `/portal/conversations/{id}/message` | POST | CustomerController@sendMessage | Send message |

---

## 🎨 Minimal Blade Views

### dashboard/chatbots/index.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container py-8">
    <h1 class="text-3xl font-bold mb-6">Chatbots</h1>
    <a href="{{ route('dashboard.chatbots.create') }}" class="btn btn-primary mb-4">+ Create</a>
    
    @forelse($chatbots as $chatbot)
        <div class="card shadow mb-4">
            <div class="card-body">
                <h2 class="card-title">{{ $chatbot->name }}</h2>
                <p class="text-sm text-gray-600">{{ $chatbot->description }}</p>
                <div class="card-actions mt-4">
                    <a href="{{ route('dashboard.chatbots.show', $chatbot) }}" class="btn btn-sm">View</a>
                    <a href="{{ route('dashboard.chatbots.edit', $chatbot) }}" class="btn btn-sm">Edit</a>
                </div>
            </div>
        </div>
    @empty
        <p class="text-gray-500">No chatbots</p>
    @endforelse
    
    {{ $chatbots->links() }}
</div>
@endsection
```

### dashboard/agent/index.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container py-8">
    <h1 class="text-3xl font-bold mb-6">My Conversations</h1>
    
    @forelse($conversations as $conversation)
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="flex justify-between">
                    <div>
                        <h3 class="font-bold">{{ $conversation->chatbot->name }}</h3>
                        <p class="text-sm text-gray-600">{{ $conversation->last_activity_at->diffForHumans() }}</p>
                    </div>
                    <a href="{{ route('dashboard.agent.show', $conversation) }}" class="btn btn-primary btn-sm">Reply</a>
                </div>
            </div>
        </div>
    @empty
        <p class="text-gray-500">No conversations</p>
    @endforelse
</div>
@endsection
```

### portal/conversations/index.blade.php
```blade
@extends('layouts.app')
@section('content')
<div class="container py-8">
    <h1 class="text-3xl font-bold mb-6">My Conversations</h1>
    
    @forelse($conversations as $conversation)
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-bold">{{ $conversation->chatbot->name }}</h3>
                        <span class="badge {{ $conversation->closed ? 'badge-error' : 'badge-success' }}">
                            {{ $conversation->closed ? 'Closed' : 'Open' }}
                        </span>
                    </div>
                    <a href="{{ route('portal.conversations.show', $conversation) }}" class="btn btn-primary btn-sm">View</a>
                </div>
            </div>
        </div>
    @empty
        <p class="text-gray-500">No conversations</p>
    @endforelse
</div>
@endsection
```

---

## ✅ Final Verification

After setup, run these:

```bash
# 1. Check routes
php artisan route:list --name=chatbot

# 2. Check policies
php artisan policy:list | grep Chatbot

# 3. Check database
php artisan tinker
>>> Schema::hasColumn('ext_chatbots', 'workspace_id')
# Output: true

>>> Schema::hasColumn('ext_chatbot_conversations', 'assigned_agent_id')
# Output: true

# 4. Test auth
>>> auth()->user()->can('manage-chatbots')
# Output: true or false (depending on permissions)

# 5. Test access
>>> \App\Models\Workspace::first()
# Output: Workspace object

>>> \App\Extensions\Chatbot\System\Models\Chatbot::first()
# Output: Chatbot object or null

>>> exit
```

---

## 🆘 Troubleshooting

| Error | Solution |
|-------|----------|
| `Target class does not exist` | Check controller path matches namespace |
| `Policy not working` | Clear cache: `php artisan cache:clear` |
| `Route not found` | Check `routes/chatbot.php` is included in RouteServiceProvider |
| `Column not exists` | Run migration: `php artisan migrate` |
| `Authorization denied` | Check user has permission and policy returns true |
| `Migration fails` | Check columns don't already exist (migration has guards) |

---

## 📞 Need Help?

1. **Check INSTALLATION_GUIDE.md** - Step-by-step instructions
2. **Check DEPENDENCIES_GUIDE.md** - What you need first
3. **Check COMPLETE_SUMMARY.md** - Full overview
4. **Run verification commands** above

---

**You're ready to ship!** 🚀
