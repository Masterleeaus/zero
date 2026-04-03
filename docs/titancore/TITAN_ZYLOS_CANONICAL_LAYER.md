# Titan Zylos Canonical Layer

> **Canonical class:** `App\TitanCore\Zylos\ZylosBridge`  
> **Deprecated path:** `App\TitanCore\Zero\Skills\ZylosBridge` (tombstone)

---

## Overview

`ZylosBridge` is the canonical integration layer between Titan Core and the Zylos external skill runtime. It serves two distinct responsibility modes:

1. **Dispatch mode** — Send HMAC-signed skill execution requests to the Zylos HTTP endpoint and handle callbacks.
2. **Admin monitor mode** — Provide status snapshots, restart/disable controls, and event log queries for the Titan Core admin panel.

Both modes are unified in the single canonical class at `App\TitanCore\Zylos\ZylosBridge`.

---

## Canonical Class

### `App\TitanCore\Zylos\ZylosBridge`

**File:** `app/TitanCore/Zylos/ZylosBridge.php`

**Registered as:** Singleton in `TitanCoreServiceProvider`

#### Constructor

```php
public function __construct(
    protected \Illuminate\Http\Client\Factory $http
)
```

---

## Public API

### Dispatch Mode

#### `dispatch()`

```php
public function dispatch(string $skillSlug, array $payload): array
```

Sends an HMAC-SHA256-signed HTTP POST request to the Zylos runtime endpoint.

- **Endpoint:** `titan_core.zylos.endpoint` (env: `ZYLOS_ENDPOINT`)
- **Signing:** Payload body HMAC-signed with `titan_core.zylos.secret` (env: `ZYLOS_SECRET`)
- **Signature header:** `X-Zylos-Signature`
- **Timeout:** `titan_core.zylos.timeout` (env: `ZYLOS_TIMEOUT`, default: `10`)
- **DB event:** Records `dispatch` event to `tz_skill_events`

**Returns:** `['ok' => bool, 'execution_id' => string, 'status' => string, ...]`

---

#### `executionStatus()`

```php
public function executionStatus(string $executionId): array
```

Queries the Zylos runtime for the current status of a dispatched execution.

- **Endpoint:** `{ZYLOS_ENDPOINT}/status/{executionId}`
- **Auth:** HMAC-signed request header

**Returns:** `['ok' => bool, 'execution_id' => string, 'status' => string, 'result' => array|null]`

---

#### `list()`

```php
public function list(): array
```

Retrieves the available skill definitions from the Zylos runtime.

- **Endpoint:** `{ZYLOS_ENDPOINT}/skills`
- **Auth:** HMAC-signed request header

**Returns:** `['ok' => bool, 'skills' => array]`

---

#### `validateCallback()`

```php
public function validateCallback(string $rawBody, string $incomingSignature): bool
```

Verifies the HMAC-SHA256 signature of an inbound Zylos callback.

- **Algorithm:** `hash_hmac('sha256', $rawBody, $secret)`
- **Constant-time comparison** to prevent timing attacks
- Called by `ValidateZylosSignature` middleware on the `POST api/titan/signal/callback` route

**Returns:** `true` if signature valid, `false` otherwise

---

### Admin Monitor Mode

#### `status()`

```php
public function status(): array
```

Returns a snapshot of all registered skills with their current state and recent event log.

- Calls `registeredSkills()` for the skill list
- Queries `tz_skill_events` for recent events per skill
- Used by `TitanCoreAdminController::skills()`

**Returns:**
```php
[
    'skills' => [
        [
            'name'         => string,
            'state'        => string,   // active|disabled|unknown
            'last_event'   => string,
            'event_count'  => int,
        ],
        ...
    ],
    'timestamp' => string,
]
```

---

#### `restart()`

```php
public function restart(string $skill): array
```

Queues a restart event for the named skill. Records to `tz_skill_events` with event type `restart`.

**Returns:** `['ok' => bool, 'skill' => string, 'event' => 'restart']`

---

#### `disable()`

```php
public function disable(string $skill): array
```

Disables the named skill and records to `tz_skill_events` with event type `disable`.

**Returns:** `['ok' => bool, 'skill' => string, 'event' => 'disable']`

---

### Protected Methods

#### `registeredSkills()`

```php
protected function registeredSkills(): array
```

Returns the configured skill list from `titan_core.skills.registered`. Falls back to `tz_skill_registry` DB table if config is empty.

#### `recordEvent()`

```php
protected function recordEvent(string $skill, string $event, array $payload): void
```

Inserts a row into `tz_skill_events` with: `skill_name`, `event_type`, `payload` (JSON), `created_at`.

---

## Configuration

All config keys live under `config/titan_core.php`:

| Config key | Env var | Default | Purpose |
|------------|---------|---------|---------|
| `titan_core.zylos.endpoint` | `ZYLOS_ENDPOINT` | `""` | HTTP endpoint of Zylos runtime |
| `titan_core.zylos.secret` | `ZYLOS_SECRET` | `""` | HMAC-SHA256 signing secret |
| `titan_core.zylos.timeout` | `ZYLOS_TIMEOUT` | `10` | HTTP request timeout (seconds) |
| `titan_core.skills.registered` | — | `[]` | Statically configured skill list |

**Note:** When `ZYLOS_ENDPOINT` is empty, `dispatch()`, `executionStatus()`, and `list()` will return failure responses without making HTTP calls. The admin monitor methods (`status()`, `restart()`, `disable()`) remain functional via the DB event log.

---

## Queue Model

Skill dispatch is not queued at the bridge level. The bridge makes a **synchronous HTTP call** to the Zylos runtime. The `titan-skills` queue is used by the MCP layer:

- `SkillDispatchHandler` may push the dispatch to the `titan-skills` queue before calling `ZylosBridge::dispatch()`, allowing the invoking request to return immediately.
- The Zylos runtime itself is responsible for async execution of the skill.

| Queue | Used by | Purpose |
|-------|---------|---------|
| `titan-skills` | `SkillDispatchHandler` | Async wrap around ZylosBridge dispatch |

Queue name is configurable: `titan_core.queues.skills` (env: `TITAN_QUEUE_SKILLS`, default: `titan-skills`).

---

## Callback Flow

Inbound callbacks from the Zylos runtime follow this path:

```
POST api/titan/signal/callback
  → ValidateZylosSignature middleware
      → ZylosBridge::validateCallback($rawBody, $header)
      → 403 on failure
  → throttle:120,1
  → McpServerController::skillCallback()
      → Records completion event
      → Dispatches follow-on signals if needed
```

The callback route does **not** require `auth:sanctum`. Signature validation is the sole authentication mechanism.

---

## Database Tables

### `tz_skill_events`

Event log for all skill-related actions (dispatch, restart, disable, callback).

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint, PK | Auto-increment |
| `skill_name` | string | Skill slug |
| `event_type` | string | `dispatch`, `restart`, `disable`, `callback` |
| `payload` | JSON | Event payload |
| `created_at` | timestamp | |

### `tz_skill_registry` (optional)

Fallback skill definition store when `titan_core.skills.registered` is empty.

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint, PK | |
| `name` | string | Skill slug |
| `state` | string | `active`, `disabled` |
| `meta` | JSON | Additional skill metadata |

---

## MCP Integration

`ZylosBridge` is the backend for three MCP capabilities:

| Capability | Bridge method |
|------------|--------------|
| `titan.skill.dispatch` | `ZylosBridge::dispatch()` |
| `titan.skill.status` | `ZylosBridge::executionStatus()` |
| `titan.skill.list` | `ZylosBridge::list()` |

All three are handled by dedicated handler classes in `app/TitanCore/MCP/Handlers/`.

---

## Deprecated Path

### `App\TitanCore\Zero\Skills\ZylosBridge`

**File:** `app/TitanCore/Zero/Skills/ZylosBridge.php`

**Status:** Tombstone — extends canonical bridge with no additional methods.

```php
/** @deprecated Use App\TitanCore\Zylos\ZylosBridge (canonical). */
class ZylosBridge extends \App\TitanCore\Zylos\ZylosBridge
{
    // Intentionally empty — extends canonical bridge for backwards compatibility.
}
```

Any existing code injecting or referencing `App\TitanCore\Zero\Skills\ZylosBridge` will continue to work (it resolves to the canonical class). New code must import `App\TitanCore\Zylos\ZylosBridge` directly.

---

## Admin Integration

`TitanCoreAdminController` injects `ZylosBridge` to power the Skills screen:

```php
public function skills(): View
    // Calls ZylosBridge::status() → passes to panel.admin.titan.core.skills view

public function skillRestart(Request $request): JsonResponse
    // Calls ZylosBridge::restart($skill)

public function skillDisable(Request $request): JsonResponse
    // Calls ZylosBridge::disable($skill)
```

Admin skill management does **not** require a live Zylos endpoint — restart/disable operate via the local DB event log.
