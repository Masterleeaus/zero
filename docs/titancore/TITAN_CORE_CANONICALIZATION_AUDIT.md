# Titan Core Canonicalization Audit

> **Pass:** Pre-Nexus canonicalization  
> **Scope:** Duplicate detection, canonical resolution, tombstone placement, binding cleanup

---

## Summary

This pass resolved class-level conflicts that would have caused PHP fatal errors at boot, consolidated duplicate service bindings in `TitanCoreServiceProvider`, and placed named tombstone stubs at deprecated namespace paths to prevent namespace drift.

---

## 1. Memory Subsystem

### Problem

Two distinct `TitanMemoryService` implementations existed with incompatible APIs:

| Class | Backing | API style | Tenancy |
|-------|---------|-----------|---------|
| `App\Titan\Core\TitanMemoryService` | DB (`tz_ai_memories`) | Typed parameters: `(int $companyId, int $userId, ...)` | `company_id` scoped |
| `App\TitanCore\Zero\Memory\TitanMemoryService` | Laravel cache | String keys: `(string $key, ?int $companyId)` | Optional |

`TitanCoreServiceProvider` previously imported both via conflicting `use` statements, causing a PHP fatal at class load time:

```php
// REMOVED (caused fatal):
use App\TitanCore\Zero\Memory\TitanMemoryService;
use App\Titan\Core\TitanMemoryService;          // ← duplicate alias
```

### Resolution

- **Canonical:** `App\Titan\Core\TitanMemoryService` — DB-backed, rewind-compatible, `company_id`-scoped.
- **Deprecated:** `App\TitanCore\Zero\Memory\TitanMemoryService` — demoted to tombstone; implements `MemoryContract` only; cache-backed path retained for contract compatibility.
- **Provider fix:** Single `use App\Titan\Core\TitanMemoryService;` binding in provider. One singleton registration.
- **MCP handler update:** `MemoryStoreHandler` and `MemoryRecallHandler` now inject `App\Titan\Core\TitanMemoryService`.

### Tombstone (deprecated path)

```php
// app/TitanCore/Zero/Memory/TitanMemoryService.php
// Retained for MemoryContract interface compatibility only.
// Do NOT inject into MCP handlers, AI router, or admin panel.
class TitanMemoryService implements MemoryContract { ... }
```

---

## 2. Zylos Subsystem

### Problem

Two distinct `ZylosBridge` classes existed with split responsibilities:

| Class | Had | Missing |
|-------|-----|---------|
| `App\TitanCore\Zylos\ZylosBridge` | `status()`, `restart()`, `disable()` | `dispatch()`, `list()`, `executionStatus()`, `validateCallback()` |
| `App\TitanCore\Zero\Skills\ZylosBridge` | `dispatch()`, `list()`, `executionStatus()`, `validateCallback()` | Admin monitor methods |

`TitanCoreServiceProvider` previously imported both:

```php
// REMOVED (caused fatal):
use App\TitanCore\Zero\Skills\ZylosBridge;
use App\TitanCore\Zylos\ZylosBridge;          // ← duplicate alias
```

### Resolution

- **Canonical:** `App\TitanCore\Zylos\ZylosBridge` — merged all methods from both sources. Full API: `status()`, `restart()`, `disable()`, `dispatch()`, `executionStatus()`, `list()`, `validateCallback()`.
- **Deprecated:** `App\TitanCore\Zero\Skills\ZylosBridge` — demoted to tombstone extending canonical.
- **Provider fix:** Single `use App\TitanCore\Zylos\ZylosBridge;` binding. One singleton registration.
- **MCP handler update:** `SkillDispatchHandler`, `SkillListHandler`, `SkillStatusHandler` now inject `App\TitanCore\Zylos\ZylosBridge` (canonical).

### Tombstone (deprecated path)

```php
// app/TitanCore/Zero/Skills/ZylosBridge.php
/** @deprecated Use App\TitanCore\Zylos\ZylosBridge (canonical). */
class ZylosBridge extends \App\TitanCore\Zylos\ZylosBridge
{
    // Intentionally empty — extends canonical bridge for backwards compatibility.
}
```

---

## 3. Contracts Subsystem

### Problem

Two parallel contract namespaces existed with method signatures defined only in the `App\Titan\Core\Contracts\*` versions:

| Interface | Namespace | State before pass |
|-----------|-----------|-------------------|
| `ProcessContract` | `App\TitanCore\Contracts` | Standalone, method definitions diverged |
| `ProcessContract` | `App\Titan\Core\Contracts` | Full canonical signatures |
| `SignalContract` | `App\TitanCore\Contracts` | Standalone |
| `SignalContract` | `App\Titan\Core\Contracts` | Full canonical signatures |

### Resolution

- **Canonical:** `App\Titan\Core\Contracts\ProcessContract` and `App\Titan\Core\Contracts\SignalContract` — full method signatures authoritative.
- **Deprecated alias:** `App\TitanCore\Contracts\ProcessContract` and `App\TitanCore\Contracts\SignalContract` — now extend canonical interfaces with empty bodies. Existing code using the old namespace continues to resolve without import changes.

```php
// app/TitanCore/Contracts/ProcessContract.php
/** @deprecated Use App\Titan\Core\Contracts\ProcessContract (canonical). */
interface ProcessContract extends \App\Titan\Core\Contracts\ProcessContract {}

// app/TitanCore/Contracts/SignalContract.php
/** @deprecated Use App\Titan\Core\Contracts\SignalContract (canonical). */
interface SignalContract extends \App\Titan\Core\Contracts\SignalContract {}
```

---

## 4. Config (`config/titan_core.php`)

### Problem

Duplicate keys existed in the `ai` section, causing the later key to silently override the earlier one.

### Resolution

- Removed duplicate entries. Each key appears exactly once in the `ai` array.
- All keys verified present: `default_runtime`, `model_router`, `minimum_confidence`, `rate_limit_per_user`, `default_text_model`, `default_image_model`.

---

## 5. Service Provider (`TitanCoreServiceProvider`)

### Changes Made

| Before | After |
|--------|-------|
| Two conflicting `use` imports for `TitanMemoryService` | Single `use App\Titan\Core\TitanMemoryService` |
| Two conflicting `use` imports for `ZylosBridge` | Single `use App\TitanCore\Zylos\ZylosBridge` |
| Duplicate singleton registration for `TitanMemoryService` | One singleton |
| Duplicate singleton registration for `ZylosBridge` | One singleton |

The provider docblock now explicitly documents canonical vs deprecated paths:

```php
/**
 * Canonical singletons:
 *   Memory runtime → App\Titan\Core\TitanMemoryService (DB-backed, rewind-compatible)
 *   Zylos bridge   → App\TitanCore\Zylos\ZylosBridge (dispatch + admin monitor)
 *
 * Deprecated paths (tombstones in place, not bound here):
 *   App\TitanCore\Zero\Memory\TitanMemoryService  — superseded
 *   App\TitanCore\Zero\Skills\ZylosBridge         — superseded
 */
```

---

## Deferred Work

| Item | Status |
|------|--------|
| Rename `App\TitanCore\Contracts\*` aliases to canonical paths across all callers | Deferred — tombstones handle compat |
| Remove cache-backed `TitanMemoryService` once all callers confirmed migrated | Deferred |
| Remove `App\TitanCore\Zero\Skills\ZylosBridge` tombstone | Deferred |
| Migrate `titan_core.memory.driver` config key to canonical memory config namespace | Deferred |
