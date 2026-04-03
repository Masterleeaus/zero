# Pre-Implementation Verification & Setup Guide

## Quick Check: Run This First

```bash
php artisan tinker

# Check if User model exists and has workspace
>>> User::first()?->workspace
# Should return a Workspace object, not null

# Check if Chatbot models exist
>>> App\Extensions\Chatbot\System\Models\Chatbot::first()
# Should return a Chatbot object

# Check tables exist
>>> Schema::getTables()
# Look for: ext_chatbots, ext_chatbot_conversations, ext_chatbot_histories, workspaces, users

# Exit tinker
>>> exit
```

---

## Missing Component Checklist & Installation

### 1. User Model & Workspace Relationship

**Check if exists:**
```bash
ls -la app/Models/User.php
php artisan tinker
>>> Schema::hasColumn('users', 'workspace_id')
```

**If workspace_id is missing, add it:**

```php
// database/migrations/2024_03_24_add_workspace_to_users.php
Schema::table('users', function (Blueprint $table) {
    $table->after('id', function (Blueprint $table) {
        $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
    });
});
```

**User Model should have:**
```php
// app/Models/User.php
class User extends Authenticatable
{
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
```

---

### 2. Workspace Model

**Check if exists:**
```bash
ls -la app/Models/Workspace.php
# Or whatever your workspace model is called (Business, Organization, etc)
```

**If missing, create it:**
```bash
php artisan make:model Workspace -m
```

**Workspace Model:**
```php
// app/Models/Workspace.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    protected $fillable = ['name', 'slug', 'owner_id', 'description'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function chatbots()
    {
        return $this->hasMany(Chatbot::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }
}
```

**Migration:**
```php
// database/migrations/2024_01_01_create_workspaces_table.php
Schema::create('workspaces', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->foreignId('owner_id')->constrained('users');
    $table->text('description')->nullable();
    $table->timestamps();
});
```

---

### 3. Service Request Model (For Escalations)

**Check if exists:**
```bash
ls -la app/Models/ServiceRequest.php
php artisan tinker
>>> Schema::hasTable('service_requests')
```

**If missing, create it:**
```bash
php artisan make:model ServiceRequest -m
```

**Service Request Model:**
```php
// app/Models/ServiceRequest.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    protected $fillable = [
        'workspace_id',
        'customer_id',
        'title',
        'description',
        'status',
        'priority',
        'assigned_to',
        'source',
        'chatbot_conversation_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function assignedAgent()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function chatbotConversation()
    {
        return $this->belongsTo(\App\Extensions\Chatbot\System\Models\ChatbotConversation::class);
    }
}
```

**Migration:**
```php
// database/migrations/2024_01_01_create_service_requests_table.php
Schema::create('service_requests', function (Blueprint $table) {
    $table->id();
    $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
    $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
    $table->string('title');
    $table->text('description')->nullable();
    $table->enum('status', ['open', 'assigned', 'in_progress', 'resolved', 'closed'])->default('open');
    $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
    $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
    $table->string('source')->default('manual'); // manual, chatbot, email, etc
    $table->foreignId('chatbot_conversation_id')->nullable()->constrained('ext_chatbot_conversations')->nullOnDelete();
    $table->timestamps();
    $table->index(['workspace_id', 'status']);
    $table->index(['assigned_to', 'status']);
});
```

---

### 4. Verify ChatBot Extension Models

**Check if models exist:**
```bash
ls -la app/Extensions/Chatbot/System/Models/
```

**Should have:**
- Chatbot.php
- ChatbotConversation.php
- ChatbotHistory.php
- ChatbotCustomer.php
- ChatbotChannel.php

**Check if relationships are set up:**

```php
// app/Extensions/Chatbot/System/Models/Chatbot.php
class Chatbot extends Model
{
    // Should have:
    public function conversations() { /* ... */ }
    public function channels() { /* ... */ }
    public function customer() { /* ... */ }
    public function workspace() { /* ... */ }
}

// app/Extensions/Chatbot/System/Models/ChatbotConversation.php
class ChatbotConversation extends Model
{
    // Should have:
    public function chatbot() { /* ... */ }
    public function customer() { /* ... */ }
    public function histories() { /* ... */ }
    public function assignedAgent() { /* return $this->belongsTo(User::class, 'assigned_agent_id'); */ }
}

// app/Extensions/Chatbot/System/Models/ChatbotHistory.php
class ChatbotHistory extends Model
{
    // Should have:
    public function conversation() { /* ... */ }
}
```

**If relationships are missing, add them to the models.**

---

### 5. Check for Required Services

```bash
# Check if GeneratorService exists
ls -la app/Extensions/Chatbot/System/Services/GeneratorService.php

# Check if ChatbotService exists
ls -la app/Extensions/Chatbot/System/Services/ChatbotService.php

# Check if ChatbotAnalyticsService exists
ls -la app/Extensions/Chatbot/System/Services/ChatbotAnalyticsService.php
```

**If any are missing, the extension isn't fully installed. Reinstall from your ZIP.**

---

### 6. Check ActivityLog (For Audit Trail)

This is optional but recommended for tracking who did what.

```bash
# Check if package is installed
composer show | grep spatie

# Install if missing
composer require spatie/laravel-activitylog
php artisan vendor:publish --provider="Spatie\ActivityLog\ActivityLogServiceProvider" --tag="migrations"
php artisan migrate
```

**Usage in controllers (already included in scaffold):**
```php
activity()
    ->causedBy(auth()->user())
    ->performedOn($chatbot)
    ->event('created')
    ->log('Chatbot created');
```

---

### 7. Check Broadcasting/Real-Time (For Ably)

**Check if Ably is configured:**
```bash
grep -i "ably\|broadcast" .env
```

**If using Ably for real-time updates:**

```bash
composer require ably/ably-php
```

**Configure in .env:**
```env
BROADCAST_DRIVER=ably
ABLY_KEY=your-ably-key-here
```

**Or use this simpler version without real-time (just removes broadcasts):**
Edit controllers and comment out:
```php
// broadcast(new ConversationAssigned($conversation, $agent));
```

---

### 8. Create Portal Controller Directory

```bash
mkdir -p app/Http/Controllers/Portal
```

---

### 9. Verify Policies Directory

```bash
# Check if exists
ls -la app/Policies/

# If not exists, create it
mkdir -p app/Policies
```

---

### 10. Verify Routes Directory

```bash
ls -la routes/
# Should have: web.php, api.php, channels.php (broadcast)
# You'll add: chatbot.php
```

---

## Step-by-Step Setup (If Starting Fresh)

### If you have NONE of the above, do this:

```bash
# 1. Create Workspace migration and model
php artisan make:model Workspace -m

# 2. Create ServiceRequest migration and model
php artisan make:model ServiceRequest -m

# 3. Add workspace_id to users
php artisan make:migration add_workspace_to_users

# 4. Install activity log
composer require spatie/laravel-activitylog
php artisan vendor:publish --provider="Spatie\ActivityLog\ActivityLogServiceProvider" --tag="migrations"

# 5. Set up directories
mkdir -p app/Policies
mkdir -p app/Http/Controllers/Portal

# 6. Run all migrations
php artisan migrate

# 7. Copy all controller/policy files
# (from the scaffolded files provided)

# 8. Create chatbot scoping migration
# (from the migration file provided)
php artisan migrate
```

---

## Minimal Setup (If You Have MagicAI Already)

If MagicAI is already installed with workspaces and chatbot extension:

```bash
# 1. Verify user has workspace_id
php artisan tinker
>>> User::first()->workspace_id
# Should not be null

# 2. Verify chatbot tables exist
>>> Schema::getTables()
# Look for ext_chatbots, ext_chatbot_conversations, etc

# 3. Create directories
mkdir -p app/Policies
mkdir -p app/Http/Controllers/Portal

# 4. Just add the migration that adds the 3 columns:
# Copy migration-chatbot-scoping.php to database/migrations/
php artisan migrate

# 5. Copy all controller/policy files

# 6. Update RouteServiceProvider
# 7. Update AuthServiceProvider
# 8. Done!
```

---

## Validation: After Setup, Run These Checks

```bash
# 1. Check migrations ran
php artisan migrate:status

# 2. Check new columns exist
php artisan tinker
>>> Schema::getColumns('ext_chatbots')
# Should include: workspace_id

>>> Schema::getColumns('ext_chatbot_conversations')
# Should include: assigned_agent_id, service_request_id

# 3. Check routes registered
php artisan route:list | grep chatbot
# Should show all dashboard/chatbots, dashboard/agent, portal/conversations routes

# 4. Check policies registered
php artisan policy:list | grep Chatbot
# Should show ChatbotPolicy and ChatbotConversationPolicy

# 5. Check models exist
>>> \App\Models\Workspace::first()
>>> \App\Models\ServiceRequest::first()
>>> \App\Extensions\Chatbot\System\Models\Chatbot::first()

# 6. Test authorization
>>> auth()->user()->can('manage-chatbots')
# Should return true/false based on permissions

exit
```

---

## Permissions/Roles Setup

If your MagicAI uses Spatie permissions/roles:

```bash
# Install if not already there
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

**Seed permissions:**
```php
// database/seeders/ChatbotPermissionsSeeder.php
namespace Database\Seeders;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ChatbotPermissionsSeeder
{
    public function run()
    {
        // Create permissions
        Permission::firstOrCreate(['name' => 'view-chatbots']);
        Permission::firstOrCreate(['name' => 'create-chatbots']);
        Permission::firstOrCreate(['name' => 'manage-chatbots']);
        Permission::firstOrCreate(['name' => 'respond-to-conversations']);
        Permission::firstOrCreate(['name' => 'manage-all-conversations']);

        // Assign to owner role
        $ownerRole = Role::firstOrCreate(['name' => 'owner']);
        $ownerRole->givePermissionTo([
            'view-chatbots',
            'create-chatbots',
            'manage-chatbots',
            'manage-all-conversations',
        ]);

        // Assign to agent role
        $agentRole = Role::firstOrCreate(['name' => 'agent']);
        $agentRole->givePermissionTo([
            'respond-to-conversations',
        ]);
    }
}
```

**Run seeder:**
```bash
php artisan db:seed --class=ChatbotPermissionsSeeder
```

---

## Common Issues & Fixes

### Issue: "Target model not found" error

**Solution:** Check model namespaces in controllers match your app structure
```php
// If your Chatbot model is in a different namespace, update:
use App\Models\Chatbot; // Instead of App\Extensions\Chatbot\System\Models\Chatbot
```

### Issue: "Class not found" in routes

**Solution:** Verify controller namespaces
```php
// In routes/chatbot.php, check:
use App\Http\Controllers\Dashboard\ChatbotCommandController;
// Make sure class exists at that path
```

### Issue: Policies not working

**Solution:** Clear authorization cache
```bash
php artisan cache:clear
php artisan config:cache
```

### Issue: Routes not showing up

**Solution:** Clear route cache and register in RouteServiceProvider
```bash
php artisan route:clear
# Then verify routes/chatbot.php is included in RouteServiceProvider
```

### Issue: Migration fails with "column already exists"

**Solution:** The migration checks for existing columns with `if (!Schema::hasColumn(...))` so it's safe to run multiple times. If it still fails:
```bash
# Check if columns exist
php artisan tinker
>>> Schema::hasColumn('ext_chatbots', 'workspace_id')

# If true, skip that part of migration
```

---

## Summary Checklist

Before copying the scaffold code, make sure you have:

- [ ] User model with workspace_id
- [ ] Workspace model
- [ ] ServiceRequest model (optional)
- [ ] ChatBot extension installed with all tables & models
- [ ] Laravel 10+ with Blade, Policies, Validation
- [ ] app/Policies directory exists
- [ ] app/Http/Controllers/Portal directory can be created
- [ ] routes/ directory exists
- [ ] Broadcasting configured (Ably or disable)
- [ ] Activity log installed (optional but recommended)
- [ ] Permissions/roles system (Spatie or custom)

**If you have 70%+ of these, you're ready to go.** The scaffold handles the rest.

---

## What You'll Do

1. ✅ Copy 3 controllers
2. ✅ Copy 2 policies
3. ✅ Create request classes
4. ✅ Copy routes file
5. ✅ Copy migration
6. ✅ Update 2 providers (RouteServiceProvider, AuthServiceProvider)
7. ✅ Run migration
8. ✅ Create Blade views (use simple templates to start)
9. ✅ Done!

**Total time: 2-4 hours** (including view creation and testing)
