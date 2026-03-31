# Copilot Task: Phase 12 — Support Feature Triage (KnowledgeBase, Notices, Team Chat)

## Context
WorkCore merge Phase 12. The support/ticketing system is partially done. Three deferred features need building:
1. **KnowledgeBase / Playbooks** — internal docs/procedures
2. **Notice Board** — company-wide announcements
3. **Team Chat** — internal messaging between staff

Per the WorkCore pre-merge decision: these are scoped as staff-internal features only (not customer-facing).

## Feature 1: KnowledgeBase / Playbooks

### Models (create if missing)
- `KnowledgeBaseCategory` — `company_id`, `name`, `slug`, `description`, `sort_order`
- `KnowledgeBaseArticle` — `company_id`, `created_by`, `category_id`, `title`, `slug`, `body` (longText), `status` (draft/published), `published_at`, `view_count`
- `KnowledgeBaseFile` — `company_id`, `article_id`, `name`, `file_path`, `file_size`

### Controller
`app/Http/Controllers/Core/Support/KnowledgeBaseController.php`
- `index()` — list articles with category filter, search
- `show(KnowledgeBaseArticle $article)` — increment `view_count`, render body as markdown
- `store/update/destroy` — admin only

### Routes (add to `routes/core/support.routes.php`)
```php
Route::resource('playbooks', KnowledgeBaseController::class)->names('dashboard.support.playbooks');
Route::resource('playbooks/categories', KnowledgeBaseCategoryController::class)->names('dashboard.support.playbook-categories');
```

### Views
- `support/playbooks/index.blade.php` — searchable grid with category sidebar
- `support/playbooks/show.blade.php` — article view with rendered markdown, file attachments
- `support/playbooks/form.blade.php` — editor with markdown/rich-text, category picker

### Menu
Add to `config/workcore.php` features: `'knowledgebase' => true` once built.
Then in MenuService add: `dashboard.support.playbooks.index` under Support group.

---

## Feature 2: Notice Board

### Models
- `Notice` — `company_id`, `created_by`, `title`, `body`, `type` (info/warning/urgent), `is_pinned`, `publish_at`, `expire_at`
- `NoticeView` — `notice_id`, `user_id`, `viewed_at` (tracks who has seen it)

### Controller
`app/Http/Controllers/Core/Support/NoticeController.php`
- `index()` — list active notices (not expired), show unread badge counts
- `markViewed(Notice $notice)` — upsert `NoticeView` record for current user
- `store/update/destroy` — admin only

### Dashboard Widget
Add a notice widget to the main dashboard: show latest 3 unread notices for the authenticated user.
```php
$notices = Notice::where('company_id', $companyId)
    ->where(fn($q) => $q->whereNull('expire_at')->orWhere('expire_at', '>', now()))
    ->whereDoesntHave('views', fn($q) => $q->where('user_id', auth()->id()))
    ->orderByDesc('is_pinned')
    ->latest()
    ->limit(3)
    ->get();
```

### Views
- `support/notices/index.blade.php` — card grid with type badge (info/warning/urgent), pin indicator
- `support/notices/form.blade.php` — title, body, type picker, publish/expire date pickers

---

## Feature 3: Team Chat (Internal Staff Messaging)

### Decision
Keep Team Chat as a **lightweight internal tool**, not full omnichannel. Use the existing `LiveNotification` broadcast channel as transport.

### Models
- `ChatRoom` — `company_id`, `name`, `type` (general/direct/group), `created_by`
- `ChatRoomMember` — `room_id`, `user_id`, `joined_at`, `last_read_at`
- `ChatMessage` — `company_id`, `room_id`, `user_id`, `body`, `is_edited`, `deleted_at` (soft deletes)
- `ChatMessageFile` — `message_id`, `company_id`, `file_path`, `file_name`, `file_size`

### Backend
`app/Http/Controllers/Core/Support/TeamChatController.php`
- `rooms()` — list rooms the user is a member of
- `messages(ChatRoom $room)` — paginated messages (cursor pagination)
- `send(Request $request, ChatRoom $room)` — store message + broadcast via Pusher/Ably
- `markRead(ChatRoom $room)` — update `last_read_at` for the user

### Broadcast Event
Create `app/Events/ChatMessageSent.php` implementing `ShouldBroadcast`:
```php
public function broadcastOn(): Channel
{
    return new PrivateChannel("chat.{$this->message->room_id}");
}
```

### Views
- `support/chat/index.blade.php` — sidebar room list + main chat pane, Alpine.js for real-time updates
- Use Echo to subscribe: `Echo.private('chat.{roomId}').listen('ChatMessageSent', ...)`

### Feature Flag
Set `'teamchat' => false` in `config/workcore.php` until tested.

---

## Create Output Doc
Create `docs/COMMS_TRIAGE_PLAN.md` with:
- Decision for each feature: merge-now / defer / archive
- Status: implemented / in-progress / pending
- Notes on omnichannel deferral (customer-facing SMS/WhatsApp/Messenger explicitly deferred)
