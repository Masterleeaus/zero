# PROVIDER_COLLISION_MAP.md

**Phase 8 — Step 2: Service Provider Collision Audit**
**Date:** 2026-04-03
**Scope:** app/Providers, app/Extensions providers, config/app.php, CodeToUse provider declarations

---

## 1. Registered Providers (config/app.php)

```
Framework Providers (standard Laravel)
App\Providers\AppServiceProvider
App\Providers\WorkCoreServiceProvider
App\Providers\AuthServiceProvider
App\Providers\BroadcastServiceProvider
App\Providers\EventServiceProvider
App\Providers\RouteServiceProvider
App\Providers\ViewServiceProvider
App\Providers\MacrosServiceProvider
App\Providers\AwsServiceProvider
App\Domains\Entity\EntityServiceProvider
App\Domains\Engine\EngineServiceProvider
App\Providers\TitanSignalsServiceProvider
App\Extensions\TitanRewind\System\TitanRewindServiceProvider
App\Providers\TitanCoreServiceProvider
App\Providers\TitanPwaServiceProvider
App\Domains\Marketplace\MarketplaceServiceProvider
Elseyyid\LaravelJsonLocationsManager\Providers\...
Barryvdh\DomPDF\ServiceProvider
Igaster\LaravelTheme\themeServiceProvider
```

Additionally, `ExtensionServiceProvider` is registered dynamically and currently delegates to `ChatbotServiceProvider`.

---

## 2. Provider-by-Provider Collision Analysis

### 2a. RouteServiceProvider

**Status:** Single registration. Loads `routes/core/*.routes.php` via glob pattern.

**Risk findings:**
- Loads ALL files matching `[a-z][a-z_]*.routes.php` — regex pattern is permissive. Any new `.routes.php` file dropped into `routes/core/` is automatically included.
- No duplicate route loader detected in other providers.
- `TitanRewindServiceProvider` does NOT register its own routes — they are loaded via `routes/core/rewind.routes.php` by the glob loader. **Safe.**

**Status: LOW risk.**

### 2b. TitanCoreServiceProvider

**Status:** Single registration in `config/app.php`.

**Container bindings registered:**
- `CoreModuleRegistry` singleton
- `ToolRegistry` singleton
- `CoreManifest` singleton
- `RuntimeCatalog` singleton
- `TitanMemoryService` singleton (canonical: `App\Titan\Core\TitanMemoryService`)
- `ZylosBridge` singleton
- `TitanAIRouter` singleton
- `ZeroCoreManager` singleton
- Multiple AI subsystem singletons

**Risk findings:**
- No duplicate bindings detected from other providers for these keys.
- Deprecated path tombstones documented in provider comments (`App\TitanCore\Zero\Memory\TitanMemoryService` and `App\TitanCore\Zero\Skills\ZylosBridge`).
- If any `CodeToUse/AI/aicore/titancore/` providers are ever registered, they may attempt to rebind these keys.

**Status: LOW risk (currently). MEDIUM risk if aicore/titancore providers are activated.**

### 2c. TitanSignalsServiceProvider

**Status:** Single registration.

**Risk findings:**
- `CodeToUse/Signals/titan_signal/TitanSignalBase/` contains an alternative signals architecture with its own routes (`routes/api.php`, `routes/titan_signals.php`).
- SQL schemas in `CodeToUse/Signals/` define `tz_signals` table — this table is also created in two core migrations (`2026_03_30_220000` and `2026_03_31_000100`). See Migration Collision Map.
- If the CodeToUse Signals bundle is integrated, provider and schema conflicts are likely.

**Status: MEDIUM risk.**

### 2d. ExtensionServiceProvider + ChatbotServiceProvider

**Status:** `ExtensionServiceProvider` is declared but NOT in `config/app.php` providers list directly — it is loaded via the normal `App\` PSR-4 path but only runs when explicitly called. It delegates to `ChatbotServiceProvider`.

**Risk findings:**
- `CodeToUse/Voice/` bundles contain their own `ChatbotWhatsappServiceProvider`, `ChatbotVoiceServiceProvider`, `ElevenLabsVoiceChatServiceProvider`, `ChatbotMessengerServiceProvider`, `ChatbotTelegramServiceProvider`.
- There are **9+ copies** of these providers across Voice passes (Pass1, Pass2, Pass3, Pass8, Pass11, Unified, MagicAI, TitanOmni Pass24, TitanOmni Pass26).
- If any of these are registered, they will attempt to boot the same middleware, bind the same container keys, and register the same routes.

**Status: HIGH risk — multiple competing provider versions exist in CodeToUse.**

### 2e. TitanPwaServiceProvider

**Status:** Single registration. Manages PWA device/signal tables and runtime.

**Risk findings:**
- No duplicate found in CodeToUse or Extensions.
- `CodeToUse/PWA/` directory exists but content is primarily frontend assets and service worker configs, not conflicting PHP providers.

**Status: LOW risk.**

### 2f. TitanRewindServiceProvider (app/Extensions/TitanRewind)

**Status:** Registered explicitly in `config/app.php`.

**Risk findings:**
- No competing `TitanRewind` provider found in `CodeToUse/`.
- Routes loaded via `routes/core/rewind.routes.php` (glob-discovered).
- Models declared in `app/Extensions/TitanRewind/System/Models/`.

**Status: LOW risk.**

### 2g. WorkCoreServiceProvider

**Status:** Single registration. Binds `VerticalLanguageResolver`, merges `workcore.php` and `verticals.php` configs.

**Risk findings:**
- `CodeToUse/WorkCore/` contains its own WorkCore codebase with potentially different service structures.
- `CodeToUse/WorkCore/WorkCore/` has a `titancore/` sub-directory with its own bootstrap, configs, and providers.
- If integrated, config key collisions on `workcore` and `verticals` are likely.

**Status: MEDIUM risk.**

### 2h. App\Domains\Entity\EntityServiceProvider + App\Domains\Engine\EngineServiceProvider

**Status:** Both registered. These are domain-scoped providers for AI entity and engine drivers.

**Risk findings:**
- `CodeToUse/AI/AICores/` bundles contain engine and entity driver implementations (e.g., `aiox-core-main`, `laravel-rag-main`, `CommerceCore-main`).
- These may attempt to register their own EntityServiceProvider/EngineServiceProvider if integrated.

**Status: MEDIUM risk.**

---

## 3. Providers in CodeToUse NOT Yet Registered

The following provider classes exist in CodeToUse but are NOT currently registered:

| Provider Class | Location | Risk if Activated |
|----------------|----------|-------------------|
| `ChatbotWhatsappServiceProvider` | CodeToUse/Voice (×9 copies) | Middleware + route conflicts |
| `ChatbotVoiceServiceProvider` | CodeToUse/Voice (×9 copies) | Container key conflicts |
| `ElevenLabsVoiceChatServiceProvider` | CodeToUse/Voice (×9 copies) | Container key conflicts |
| `ChatbotMessengerServiceProvider` | CodeToUse/Voice (×3 copies) | Route + binding conflicts |
| `ChatbotTelegramServiceProvider` | CodeToUse/Voice (×3 copies) | Route + binding conflicts |
| `RegistrationServiceProvider` | CodeToUse/Extensions/CheckoutRegistration | May rebind auth/user pipeline |
| `LiveCustomizerServiceProvider` | CodeToUse/Extensions/LiveCustomizer | Menu migration conflict |
| `CanvasServiceProvider` | CodeToUse/Extensions/Canvas | TipTap content table migration |

---

## 4. Providers Executing Migrations Automatically

| Provider | Auto-Migration Risk |
|----------|-------------------|
| `TitanRewindServiceProvider` | No auto-migration detected |
| `TitanPwaServiceProvider` | No auto-migration detected |
| `TitanCoreServiceProvider` | No auto-migration detected |
| `MarketplaceServiceProvider` | Needs inspection — marketplace packages may publish migrations |
| `RegistrationServiceProvider` (CodeToUse) | Contains migration in `/database/migrations/` — may publish |
| `LiveCustomizerServiceProvider` (CodeToUse) | Contains migration `2024_05_08_163635_menu_migrate.php` — may conflict with core menu migrations |

---

## 5. Summary Table

| Risk Level | Finding |
|------------|---------|
| **HIGH** | 9+ competing copies of Chatbot provider suite in CodeToUse/Voice — activating any will create middleware/route/binding collisions |
| **MEDIUM** | CodeToUse/Signals provider would conflict with TitanSignalsServiceProvider if registered |
| **MEDIUM** | CodeToUse/WorkCore titancore bootstrap may rebind WorkCoreServiceProvider keys |
| **MEDIUM** | AI domain providers in CodeToUse/AI may rebind EntityServiceProvider/EngineServiceProvider |
| **LOW** | All currently registered providers appear clean — no duplicate registrations detected |
| **LOW** | LiveCustomizer menu migration may conflict with core menus if activated |
