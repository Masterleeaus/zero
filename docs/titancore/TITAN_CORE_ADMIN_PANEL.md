# TITAN CORE ADMIN PANEL

## Overview

The Titan Core Admin Panel provides operational visibility and governance for the Titan AI system. It is mounted at `/dashboard/admin/titan/core` and is accessible only to administrators (`admin` middleware).

## Access

- **URL Prefix**: `/dashboard/admin/titan/core`
- **Route Name Prefix**: `admin.titan.core.`
- **Controller**: `App\Http\Controllers\Admin\TitanCore\TitanCoreAdminController`
- **Middleware**: `auth`, `admin`, `updateUserActivity`

## Panel Sections

### Models (`/models`)
Displays the active AI model routing configuration sourced from `config/titan_ai.php`. Allows per-intent model overrides for:
- `text.complete`
- `image.generate`
- `voice.synthesize`
- `agent.task`
- `code.assist`

Persists changes directly to `config/titan_ai.php`.

### Signals (`/signals`)
Monitors the `tz_signal_queue` table with filterable columns:
- `company_id` filter
- `signal_type` filter
- `broadcast_status` filter (pending / async / awaiting_approval / failed / dispatched)
- Age filter (1h / 6h / 24h / 48h / 7d)

Shows summary statistics for pending, async, awaiting, failed, and retry signals.

### Memory (`/memory`)
Displays usage statistics for the TitanCore memory subsystem:
- `tz_ai_memories` – total memory entries
- `tz_ai_memory_embeddings` – embedding count
- `tz_ai_memory_snapshots` – snapshot count
- `tz_ai_session_handoffs` – session continuity chains

Actions available:
- **Purge Expired** – deletes memory entries where `expires_at <= now()`
- **Summarise Sessions** – dispatches `titan:memory:summarise` artisan command

### Skills (`/skills`)
Monitors Zylos-managed skill processes via `ZylosBridge`:
- Registered skill count
- Running / Failed counts
- Per-skill last event, heartbeat, and last payload inspection

Actions: Restart skill, Disable skill.

### Activity (`/activity`)
Real-time WebSocket-backed activity feed on channel `titan.core.activity`. Also shows the last 100 `tz_audit_log` entries server-side.

### Budgets (`/budgets`)
Manages token budget enforcement via `config/titan_budgets.php`:
- Daily platform limit
- Per-request maximum
- Per-user daily cap
- Per-company daily cap
- Per-intent caps
- Budget-exceeded action (deny / fallback_model / notify_admin)

### Queues (`/queues`)
Displays job counts for dedicated Titan queues:
- `titan-ai`
- `titan-signals`
- `titan-skills`
- `default`

Actions: Retry failed jobs, Flush queue.

### Health (`/health`)
System health checks with pass/fail indicators for:
- Router, Kernel, Memory service, Signal pipeline, Rewind hooks, Zylos Bridge, Queue workers, MCP HTTP transport

## Views Location

All views are located at:
```
resources/views/default/panel/admin/titan/core/
├── models.blade.php
├── signals.blade.php
├── memory.blade.php
├── skills.blade.php
├── activity.blade.php
├── budgets.blade.php
├── queues.blade.php
└── health.blade.php
```

All views extend `panel.layout.app` consistent with the host admin theme.
