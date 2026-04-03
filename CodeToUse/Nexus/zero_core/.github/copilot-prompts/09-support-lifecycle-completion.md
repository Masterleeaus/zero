# Copilot Task: Complete Support Ticket Lifecycle

## Context
Laravel 10 SaaS. The support/ticketing system exists (`UserSupport` model, `SupportLifecycleService`) but is missing:
- Notification routing to support staff when tickets are created/updated
- Assignment queue and prioritization
- Escalation rules

## Files
- `app/Services/Support/SupportLifecycleService.php`
- `app/Models/UserSupport.php`
- `app/Models/UserSupportMessage.php`
- `app/Http/Controllers/` (find support controller with `grep -rn "UserSupport" app/Http/Controllers/`)

## Your Task

### 1. Read the current SupportLifecycleService
Understand what status transitions exist.

### 2. Add notification on ticket creation
In the support controller's `store()` method (or in `SupportLifecycleService::open()`), after creating a ticket:
```php
// Notify all admin users in the same company
$admins = User::where('company_id', $ticket->company_id)
    ->whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'support']))
    ->get();

foreach ($admins as $admin) {
    $admin->notify(new LiveNotification(
        message: "New support ticket: {$ticket->subject}",
        link: route('dashboard.user.support.show', $ticket),
        title: 'New Support Ticket'
    ));
}
```

### 3. Add notification on status change
In `SupportLifecycleService`, after each status transition, notify the ticket owner:
```php
$ticket->user->notify(new LiveNotification(
    message: "Your ticket '{$ticket->subject}' status changed to: {$newStatus}",
    link: route('dashboard.user.support.show', $ticket),
    title: 'Ticket Updated'
));
```

### 4. Add `assignTo()` method to SupportLifecycleService
```php
public function assignTo(UserSupport $ticket, int $userId): UserSupport
{
    $ticket->update([
        'assigned_to' => $userId,
        'status' => 'waiting_on_team',
    ]);

    $assignee = User::find($userId);
    $assignee?->notify(new LiveNotification(
        message: "Support ticket assigned to you: {$ticket->subject}",
        link: route('dashboard.user.support.show', $ticket),
        title: 'Ticket Assigned'
    ));

    return $ticket->fresh();
}
```

### 5. Add `escalate()` method
```php
public function escalate(UserSupport $ticket, string $reason = ''): UserSupport
{
    $ticket->update(['priority' => 'high', 'status' => 'escalated']);

    // Notify company admins
    // ... same notification pattern as step 2

    return $ticket->fresh();
}
```

### 6. Check if `assigned_to` and `priority` columns exist
Run: `php artisan tinker --execute="Schema::hasColumn('user_support', 'assigned_to');"`
If not, create a migration to add them.

## Constraints
- Use `LiveNotification` class (already exists at `app/Notifications/LiveNotification.php`)
- Keep status transitions inside `SupportLifecycleService`, not in controllers
- All queries must be company-scoped
