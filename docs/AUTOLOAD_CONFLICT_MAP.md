# AUTOLOAD_CONFLICT_MAP.md

**Phase 8 — Step 1: Composer Autoload Conflict Audit**
**Date:** 2026-04-03
**Scope:** composer.json PSR-4 map, CodeToUse namespace declarations, core app namespaces

---

## 1. PSR-4 Autoload Configuration (composer.json)

```json
"psr-4": {
    "App\\":           "app/",
    "App\\Extensions\\": "CodeToUse/",
    "Database\\Factories\\": "database/factories/",
    "Database\\Seeders\\": "database/seeders/",
    "Database\\Helpers\\": "database/helpers/"
}
```

### CRITICAL: Dual Mapping of `App\Extensions\`

`App\Extensions\` is mapped to **two** effective roots:

| Path | Reason |
|------|--------|
| `app/Extensions/` | PSR-4 implicit via `App\` → `app/` |
| `CodeToUse/` | Explicit `App\Extensions\` → `CodeToUse/` override |

**Conflict:** Any class declared with `namespace App\Extensions\...` inside `app/Extensions/` will conflict with CodeToUse's `App\Extensions\` mapping. PHP's Composer autoloader will use the **first match** it finds — which depends on the order of autoload entries. This means classes in `app/Extensions/` and `CodeToUse/` claiming the same `App\Extensions\*` namespace are competing.

**Verified affected paths:**
- `app/Extensions/TitanRewind/System/TitanRewindServiceProvider.php` → `App\Extensions\TitanRewind\System\TitanRewindServiceProvider`
- `CodeToUse/Extensions/ExtensionLibrary/Canvas/Canvas/System/CanvasServiceProvider.php` → declares `namespace App\Extensions\...` in some files

---

## 2. Namespace Collision Detection

### 2a. High-frequency namespaces appearing in BOTH `app/` and `CodeToUse/`

| Namespace | `app/` count | `CodeToUse/` count | Risk |
|-----------|-------------|-------------------|------|
| `App\Models` | 698 files | CodeToUse contains 2,745 Model files | **HIGH** — namespace clash |
| `App\Http\Controllers` | 417 files | Hundreds in CodeToUse | **HIGH** |
| `App\Observers` | 316 files | Present in CodeToUse bundles | MEDIUM |
| `App\Notifications` | 280 files | Present in CodeToUse bundles | MEDIUM |
| `App\Events` | 222 files | Present in CodeToUse bundles | MEDIUM |
| `App\Listeners` | 206 files | Present in CodeToUse bundles | MEDIUM |
| `App\Console\Commands` | 104 files | Present in multiple CodeToUse passes | MEDIUM |

### 2b. Extension Namespaces in CodeToUse Overlapping Core

The following CodeToUse bundles declare namespaces that map into active host extension paths:

| CodeToUse Path | Declared Namespace | Conflict With |
|---------------|-------------------|---------------|
| `CodeToUse/Voice/*/app/Extensions/ChatbotWhatsapp/` | `App\Extensions\ChatbotWhatsapp\...` | Potential host extension registration |
| `CodeToUse/Voice/*/app/Extensions/ChatbotVoice/` | `App\Extensions\ChatbotVoice\...` | Chatbot service provider in host |
| `CodeToUse/Voice/*/app/Extensions/ElevenlabsVoiceChat/` | `App\Extensions\ElevenlabsVoiceChat\...` | ElevenLabs registration |
| `CodeToUse/Voice/*/app/Extensions/ChatbotMessenger/` | `App\Extensions\ChatbotMessenger\...` | Messenger extension |
| `CodeToUse/Voice/*/app/Extensions/ChatbotTelegram/` | `App\Extensions\ChatbotTelegram\...` | Telegram extension |

**Note:** These are currently in `CodeToUse/` and not autoloaded unless the `App\Extensions\` PSR-4 mapping resolves them. Given the dual-mapping risk above, any future `composer dump-autoload` pass may pick up these files unexpectedly.

### 2c. GraphQL Namespace (Vendor-Like)

`CodeToUse/Comms/ably-archive/` contains a vendored copy of packages including:
- `GraphQL\Language\AST` (114 declarations)
- `GraphQL\Validator\Rules` (84 declarations)
- `GraphQL\Type\Definition` (80 declarations)

These shadow the canonical `webonyx/graphql-php` vendor package if installed. **Risk: vendor class shadowing.**

### 2d. Modules Namespace Conflicts

```
namespace Modules\Inspection\Tests\Feature  (81 occurrences in CodeToUse)
namespace Modules\QualityControl\Tests\Feature  (80 occurrences in CodeToUse)
```

The `Modules\` namespace is not registered in `composer.json` autoload. These files will not autoload currently but represent a future integration conflict if a `Modules\` PSR-4 entry is added.

### 2e. `WpOrg\Requests\Exception\Http` Namespace

132 files in `CodeToUse/Comms/ably-archive/` declare `namespace WpOrg\Requests\Exception\Http`. This shadows the WordPress Requests library if it is a vendor dependency. **Risk: vendor shadow if `rmccue/requests` or `wordpress/requests` is added.**

---

## 3. Files Autoloaded via `autoload.files`

| File | Risk |
|------|------|
| `app/Helpers/helpers.php` | No conflict detected |
| `app/Services/AdsenseService.php` | Unusual — service class in `files` autoload. Side-effect risk if loaded multiple times |
| `app/Support/workcore_helpers.php` | No conflict detected |

---

## 4. Summary Table

| Risk Level | Finding |
|------------|---------|
| **CRITICAL** | `App\Extensions\` PSR-4 dual-mapping — `app/Extensions/` and `CodeToUse/` both serve this namespace |
| **HIGH** | 2,745+ model files in `CodeToUse/` declare `App\Models\*` — namespace pollution risk during integration |
| **HIGH** | GraphQL vendor bundle inside `CodeToUse/Comms/ably-archive/` shadows webonyx package |
| **MEDIUM** | Multiple Voice pass bundles (Pass1, Pass2, Pass3, Pass8, Pass11, Unified) each declare same `App\Extensions\Chatbot*` namespaces — duplicated namespace registrations |
| **MEDIUM** | `WpOrg\Requests` shadow in ably-archive |
| **LOW** | `Modules\` namespace used in CodeToUse tests but not registered in composer — latent conflict |
| **LOW** | `AdsenseService.php` in files autoload — non-standard pattern |

---

## 5. Recommended Actions (Post-Audit — Do Not Execute Yet)

- [ ] Resolve `App\Extensions\` PSR-4 dual mapping: either rename `app/Extensions/` classes or separate CodeToUse to a different namespace root
- [ ] Remove or isolate `CodeToUse/Comms/ably-archive/` vendor dump from autoload scope
- [ ] Audit all Voice pass bundles for the canonical version before integration
- [ ] Add `Modules\` to composer only when deliberately integrating those modules
