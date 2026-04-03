# TITAN OMNI — PASS 06: Conversation Inbox + Thread UI

**Labels:** `titan-omni` `pass-06` `ui` `inbox` `livewire` `do-not-activate`
**Milestone:** Titan Omni Implementation
**Depends on:** Pass 05 complete

---

## Global Instruction

Re-read all prior pass outputs before building. Reuse host Blade/Livewire/component system. Do not build a separate frontend stack. Align to existing panel patterns.

**Doctrine:** reuse → extend → refactor → repair → replace only if unavoidable.

---

## Goal

Build the first operator-facing Omni UI — inbox list and conversation thread view.

---

## Read First — Docs

- `docs/titan-omni/conversation-graph/TITAN_OMNI_CONVERSATION_GRAPH.md`
- `docs/titan-omni/controllers/TITAN_OMNI_CONTROLLER_MAP.md`
- `docs/titan-omni/source-maps/TITAN_OMNI_ROUTE_MAP.md`
- `docs/titan-omni/reception-engine/TITAN_OMNI_RECEPTION_IMPLEMENTATION_REPORT.md` (Pass 05 output)
- Any existing panel/dashboard UI docs in `docs/`
- Existing host views in `resources/views/default/panel/` to understand conventions

---

## Read First — CodeToUse

- `CodeToUse/MarketingBot*` — donor inbox/campaign UI
- `CodeToUse/comms*` — donor comms UI if present
- `CodeToUse/TitanTalk*` — any chat/thread/inbox UI
- `CodeToUse/extension_library*` — utility UI components

---

## Build

### Inbox List
- `resources/views/default/panel/user/omni/inbox/index.blade.php`
- Show all conversations for company, ordered by last activity
- Filter by: channel, status (open/pending/resolved/review), assigned operator, unread only
- Each row: contact name/identifier, channel badge, last message preview, delivery state, intent label, time

### Conversation Thread View
- `resources/views/default/panel/user/omni/inbox/show.blade.php`
- Full message thread in chronological order
- Per-message: sender, channel badge, delivery state badge, timestamp, attachment indicator
- Side panel: contact identity card (linked host Customer/Lead if matched), conversation metadata, intent, host object links
- Compose area: text input + send button + attachment support + channel selector (if multi-channel contact)
- Action buttons: Resolve, Assign, Escalate, Add Note

### Livewire Components (if Livewire is used in host)
- `app/Livewire/Omni/InboxList.php` — real-time inbox updates
- `app/Livewire/Omni/ConversationThread.php` — live thread with polling/push

### Review Queue
- `resources/views/default/panel/user/omni/review/index.blade.php`
- Conversations where identity is unresolved or intent is low-confidence
- Actions: manually link to contact, confirm identity, dismiss, escalate

### Controllers
- `app/Http/Controllers/Omni/InboxController.php`
  - `index()` — inbox list
  - `show(Conversation $conversation)` — thread view
  - `resolve(Conversation $conversation)`
  - `assign(Request $request, Conversation $conversation)`
- `app/Http/Controllers/Omni/ReviewController.php`
  - `index()` — review queue
  - `linkContact(Request $request, Conversation $conversation)`
  - `confirmIdentity(Request $request, Conversation $conversation)`

### Routes
- `routes/omni/web.php` — dashboard-authenticated Omni web routes
- Register under `dashboard.omni.` prefix

---

## Rules

- Reuse host Blade/Livewire/component system — match existing panel conventions exactly
- Preserve existing theme — no custom CSS frameworks
- Do not build a separate frontend stack
- Delivery state badges must match the delivery model statuses from Pass 02/03
- UI must be usable without JavaScript if Livewire is unavailable (graceful fallback)

---

## Required Output

- Views in `resources/views/default/panel/user/omni/`
- Livewire components in `app/Livewire/Omni/` (if applicable)
- Controllers in `app/Http/Controllers/Omni/`
- Routes in `routes/omni/web.php`
- `docs/titan-omni/conversation-graph/TITAN_OMNI_UI_IMPLEMENTATION_REPORT.md`

---

## Pass Delivery Rules

1. What was scanned
2. What donor UI code was reused
3. What host view patterns were followed
4. What files were created
5. What docs were updated
6. What remains for Pass 07
