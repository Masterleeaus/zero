# AUTOLOAD_STABILISATION_REPORT.md

**Phase 9 — Step 2: Autoload Stabilisation**
**Date:** 2026-04-04
**Pass:** Phase 9 — Critical Structural Stabilisation

---

## Problem Identified

`composer.json` contained a dual PSR-4 mapping:

```json
"App\\": "app/",
"App\\Extensions\\": "CodeToUse/"
```

### Why This Was Critical

PHP's Composer autoloader processes PSR-4 entries in order. The `App\Extensions\` key mapped to **two effective roots**:

1. `app/Extensions/` — implicit via `App\` → `app/` (catches `App\Extensions\*` naturally)
2. `CodeToUse/` — explicit `App\Extensions\` override

Any class in `app/Extensions/` declaring `namespace App\Extensions\...` (e.g. `TitanRewindServiceProvider`) would compete with any file in `CodeToUse/` also declaring `namespace App\Extensions\...`.

**Confirmed affected host classes:**
- `app/Extensions/TitanRewind/System/TitanRewindServiceProvider.php` → `App\Extensions\TitanRewind\System\TitanRewindServiceProvider`

**Confirmed CodeToUse paths with matching namespace pattern:**
- `CodeToUse/Voice/*/app/Extensions/ChatbotWhatsapp/`
- `CodeToUse/Voice/*/app/Extensions/ChatbotVoice/`
- `CodeToUse/Voice/*/app/Extensions/ElevenlabsVoiceChat/`
- `CodeToUse/Voice/*/app/Extensions/ChatbotMessenger/`
- `CodeToUse/Voice/*/app/Extensions/ChatbotTelegram/`

Additionally, `CodeToUse/` contains:
- `App\Models` namespace: 2,745 files (vs 698 in app/)
- `App\Http\Controllers` namespace: hundreds of files
- GraphQL vendor shadows (`GraphQL\Language\AST`, `GraphQL\Validator\Rules`, etc.)

These were all silently resolvable through the `App\Extensions\` → `CodeToUse/` mapping, creating runtime ambiguity on `composer dump-autoload`.

---

## Fix Applied

**Removed** the `"App\\Extensions\\": "CodeToUse/"` entry from `composer.json` autoload.

```json
// BEFORE
"psr-4": {
    "App\\": "app/",
    "App\\Extensions\\": "CodeToUse/",
    ...
}

// AFTER
"psr-4": {
    "App\\": "app/",
    ...
}
```

### Effect

- `CodeToUse/` is no longer resolvable via Composer autoload.
- `app/Extensions/` classes continue to resolve correctly under `App\Extensions\` via the `App\` root mapping.
- No active host class is broken — all active extension providers (TitanRewind, Chatbot, etc.) live in `app/Extensions/` and resolve through `App\` → `app/`.
- CodeToUse source bundles are now correctly treated as archived source material, not live classes.

---

## Remaining Risks

| Risk | Status |
|------|--------|
| Any existing `CodeToUse/` class that was actively required by the host | NONE confirmed. CodeToUse is a source archive; its classes are not imported by active host code. |
| `composer dump-autoload` needed after this change | YES — must be run before deploying to any environment |

---

## Next Action Required

After merging this change, run:

```bash
composer dump-autoload
```

This will regenerate the classmap without the `CodeToUse/` mapping.
