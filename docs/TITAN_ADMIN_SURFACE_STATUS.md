# Titan Admin Surface Status

> **Controller:** `App\Http\Controllers\Admin\TitanCore\TitanCoreAdminController`  
> **Route file:** `routes/core/titan_admin.routes.php`  
> **Route prefix:** `dashboard/admin/titan/core`  
> **Route name prefix:** `admin.titan.core.`  
> **Middleware:** `auth`, `admin`, `updateUserActivity`  
> **View path:** `resources/views/default/panel/admin/titan/core/`

---

## Overview

The Titan Core admin surface provides 8 screens covering every operational aspect of the AI runtime: model routing, signal queue monitoring, memory management, skill control, audit activity, token budgets, queue health, and system health checks. All screens share a single controller with constructor injection of:

- `CoreKernel $kernel`
- `TitanAIRouter $router`
- `AuditTrail $auditTrail`
- `CreditsService $credits`
- `ZylosBridge $zylos`

---

## Screen 1 — Model Routing (Phase 5.2)

| Field | Value |
|-------|-------|
| Route name | `admin.titan.core.models` |
| Method | `GET dashboard/admin/titan/core/models` |
| Update route | `POST dashboard/admin/titan/core/models` → `admin.titan.core.models.update` |
| Controller methods | `models()`, `modelsUpdate(Request)` |
| View | `panel.admin.titan.core.models` |

**Data sources:**

- `TitanAIRouter::status()` → `$routerStatus` — live router capability status
- `config('titan_ai')` → `$aiConfig` — full AI config array
- `$intents` — 5 intent slots: `text.complete`, `image.generate`, `voice.synthesize`, `agent.task`, `code.assist`

**Update validates:** `default_text_model`, `default_image_model`, `intents` (array)

**Status:** ✅ Complete

---

## Screen 2 — Signal Queue Monitor (Phase 5.3)

| Field | Value |
|-------|-------|
| Route name | `admin.titan.core.signals` |
| Method | `GET dashboard/admin/titan/core/signals` |
| Controller method | `signals(Request)` |
| View | `panel.admin.titan.core.signals` |

**Data sources:**

- `tz_signal_queue` table — paginated signal queue entries
- Query filters (all optional GET params):
  - `company_id` — filter by tenant
  - `signal_type` — filter by signal type
  - `status` — filter by `broadcast_status`
  - `age` — filter to signals within last N hours

**Status:** ✅ Complete

---

## Screen 3 — Memory Usage Panel (Phase 5.4)

| Field | Value |
|-------|-------|
| Route name | `admin.titan.core.memory` |
| Method | `GET dashboard/admin/titan/core/memory` |
| Purge route | `POST dashboard/admin/titan/core/memory/purge` → `admin.titan.core.memory.purge` |
| Summarise route | `POST dashboard/admin/titan/core/memory/summarise` → `admin.titan.core.memory.summarise` |
| Controller methods | `memory()`, `memoryPurge()`, `memorySummarise()` |
| View | `panel.admin.titan.core.memory` |

**Data sources:**

- Row counts from all 4 memory tables:
  - `tz_ai_memories`
  - `tz_ai_memory_embeddings`
  - `tz_ai_memory_snapshots`
  - `tz_ai_session_handoffs`
- `$importanceDist` — importance score distribution from `tz_ai_memories` (grouped by score buckets)
- `$expirySoon` — count of entries expiring within 24 hours

**Actions:**

- `memoryPurge()` — deletes expired entries across memory tables (`expires_at < now()`)
- `memorySummarise()` — dispatches memory summarization job to `titan-ai` queue

**Status:** ✅ Complete

---

## Screen 4 — Skill Runtime Monitor (Phase 5.5)

| Field | Value |
|-------|-------|
| Route name | `admin.titan.core.skills` |
| Method | `GET dashboard/admin/titan/core/skills` |
| Restart route | `POST dashboard/admin/titan/core/skills/restart` → `admin.titan.core.skills.restart` |
| Disable route | `POST dashboard/admin/titan/core/skills/disable` → `admin.titan.core.skills.disable` |
| Controller methods | `skills()`, `skillRestart(Request)`, `skillDisable(Request)` |
| View | `panel.admin.titan.core.skills` |

**Data sources:**

- `ZylosBridge::status()` → `$skillStatus` — snapshot of all registered skills with event log

**Actions (JSON responses):**

- `skillRestart(Request)` — calls `ZylosBridge::restart($skill)`, returns `JsonResponse`
- `skillDisable(Request)` — calls `ZylosBridge::disable($skill)`, returns `JsonResponse`

**Notes:** Restart/disable operations work via the local DB event log even when `ZYLOS_ENDPOINT` is not configured. Live skill execution status requires an active Zylos runtime.

**Status:** ✅ Complete

---

## Screen 5 — Activity Feed (Phase 5.6)

| Field | Value |
|-------|-------|
| Route name | `admin.titan.core.activity` |
| Method | `GET dashboard/admin/titan/core/activity` |
| Controller method | `activity()` |
| View | `panel.admin.titan.core.activity` |

**Data sources:**

- `tz_audit_log` table — last 100 entries ordered by `created_at` DESC
- Each row cast to array; all columns displayed

**Status:** ✅ Complete

---

## Screen 6 — Token Budgets (Phase 5.7)

| Field | Value |
|-------|-------|
| Route name | `admin.titan.core.budgets` |
| Method | `GET dashboard/admin/titan/core/budgets` |
| Update route | `POST dashboard/admin/titan/core/budgets` → `admin.titan.core.budgets.update` |
| Controller methods | `budgets()`, `budgetsUpdate(Request)` |
| View | `panel.admin.titan.core.budgets` |

**Data sources:**

- `config('titan_budgets')` → `$budgetsConfig` — full budget configuration array

**Update validates:**

| Field | Rule |
|-------|------|
| `per_user_daily` | nullable, integer, min:0 |
| `per_company_daily` | nullable, integer, min:0 |
| `per_request_max` | nullable, integer, min:0 |
| `daily_limit` | nullable, integer, min:0 |
| `intents` | nullable, array |
| `intents.*` | nullable, integer, min:0 |

**Status:** ✅ Complete

---

## Screen 7 — Queue Dashboard (Phase 5.8)

| Field | Value |
|-------|-------|
| Route name | `admin.titan.core.queues` |
| Method | `GET dashboard/admin/titan/core/queues` |
| Retry route | `POST dashboard/admin/titan/core/queues/retry` → `admin.titan.core.queues.retry` |
| Flush route | `POST dashboard/admin/titan/core/queues/flush` → `admin.titan.core.queues.flush` |
| Controller methods | `queues()`, `queueRetryFailed(Request)`, `queueFlush(Request)` |
| View | `panel.admin.titan.core.queues` |

**Data sources:**

- `jobs` table — pending count per queue
- `failed_jobs` table — failed count per queue
- Monitored queues: `titan-ai`, `titan-signals`, `titan-skills`, `default`

**Actions:**

- `queueRetryFailed(Request)` — retries failed jobs for the specified queue name
- `queueFlush(Request)` — deletes all pending jobs from the specified queue

**Status:** ✅ Complete

---

## Screen 8 — Health Dashboard (Phase 5.9 / 5.14)

| Field | Value |
|-------|-------|
| Route name | `admin.titan.core.health` |
| Method | `GET dashboard/admin/titan/core/health` |
| JSON API route | `GET dashboard/admin/titan/core/health/api` → `admin.titan.core.health.api` |
| Controller methods | `health()`, `healthApi()` |
| View | `panel.admin.titan.core.health` |

**Health checks performed** (via private `runHealthChecks()`):

| Check key | Data source | Pass condition |
|-----------|-------------|----------------|
| `router` | `TitanAIRouter::status()` | No exception |
| `kernel` | `CoreKernel::status()` | Status array non-empty |
| `memory_service` | `DB::table('tz_ai_memories')->limit(1)` | No exception |
| `signal_pipeline` | `DB::table('tz_signal_queue')->limit(1)` | No exception |
| `rewind_hooks` | `config('titan-rewind')` | Config non-empty |
| `zylos_bridge` | `ZylosBridge::status()` | No exception |
| `queue_workers` | `DB::table('jobs')->limit(1)` | No exception |
| `mcp_http` | `HTTP GET {MCP_HTTP_URL}/health` | 200 response (only checked when `MCP_HTTP_URL` set) |

Each check returns: `['pass' => bool, 'detail' => string]`

**JSON API:** `GET admin.titan.core.health.api` returns the same array as `healthApi(): JsonResponse`. Suitable for external monitoring or uptime checks.

**Status:** ✅ Complete

---

## View File Inventory

All views are located at `resources/views/default/panel/admin/titan/core/`:

| File | Screen |
|------|--------|
| `models.blade.php` | Model routing |
| `signals.blade.php` | Signal queue monitor |
| `memory.blade.php` | Memory usage panel |
| `skills.blade.php` | Skill runtime monitor |
| `activity.blade.php` | Activity feed |
| `budgets.blade.php` | Token budgets |
| `queues.blade.php` | Queue dashboard |
| `health.blade.php` | Health dashboard |
