# PROVIDER_STABILISATION_REPORT.md

**Phase 9 — Step 6: Provider Stabilisation**
**Date:** 2026-04-04
**Pass:** Phase 9 — Critical Structural Stabilisation

---

## Active Provider Audit

All providers currently registered in `config/app.php`:

| Provider | Status | Risk |
|----------|--------|------|
| Laravel framework providers (Auth, Bus, Cache, etc.) | ✅ Standard | None |
| `Spatie\Permission\PermissionServiceProvider` | ✅ Single | None |
| `App\Providers\AppServiceProvider` | ✅ Single | None |
| `App\Providers\WorkCoreServiceProvider` | ✅ Single | None |
| `App\Providers\AuthServiceProvider` | ✅ Single | None |
| `App\Providers\BroadcastServiceProvider` | ✅ Single | None |
| `App\Providers\EventServiceProvider` | ✅ Single | None |
| `App\Providers\RouteServiceProvider` | ✅ Single | None |
| `App\Providers\ViewServiceProvider` | ✅ Single | None |
| `App\Providers\MacrosServiceProvider` | ✅ Single | None |
| `App\Providers\AwsServiceProvider` | ✅ Single | None |
| `App\Domains\Entity\EntityServiceProvider` | ✅ Single | None |
| `App\Domains\Engine\EngineServiceProvider` | ✅ Single | None |
| `App\Providers\TitanSignalsServiceProvider` | ✅ Single | None |
| `App\Extensions\TitanRewind\System\TitanRewindServiceProvider` | ✅ Single — resolves via `app/Extensions/` (canonical) | None |
| `App\Providers\TitanCoreServiceProvider` | ✅ Single | None |
| `App\Providers\TitanPwaServiceProvider` | ✅ Single | None |
| `App\Providers\AdminServiceProvider` | ✅ Single | None |
| `App\Domains\Marketplace\MarketplaceServiceProvider` | ✅ Single | None |
| `Elseyyid\LaravelJsonLocationsManager\...` | ✅ Vendor | None |
| `Barryvdh\DomPDF\ServiceProvider` | ✅ Vendor | None |
| `Igaster\LaravelTheme\themeServiceProvider` | ✅ Vendor | None |

**Finding: No duplicate provider registrations detected in config/app.php.**

---

## TitanRewindServiceProvider Path Fix

With the removal of `"App\\Extensions\\": "CodeToUse/"` from `composer.json` (Step 2), the `TitanRewindServiceProvider` is now resolved exclusively through the `App\` → `app/` PSR-4 mapping:

```
App\Extensions\TitanRewind\System\TitanRewindServiceProvider
→ app/Extensions/TitanRewind/System/TitanRewindServiceProvider.php ✅
```

Previously there was a risk that Composer could resolve this class from `CodeToUse/` via the removed mapping. That risk is now eliminated.

---

## CodeToUse Provider Isolation

The following provider classes exist in `CodeToUse/` and are NOT registered:

| Provider | Location | Risk if Activated |
|----------|----------|-------------------|
| `ChatbotWhatsappServiceProvider` | `CodeToUse/Voice/` (×2 retained canonical candidates) | Middleware + route conflicts |
| `ChatbotVoiceServiceProvider` | `CodeToUse/Voice/` (×2 retained) | Container key conflicts |
| `ElevenLabsVoiceChatServiceProvider` | `CodeToUse/Voice/` (×2 retained) | Container key conflicts |
| `ChatbotMessengerServiceProvider` | `CodeToUse/Voice/` (×2 retained) | Route + binding conflicts |
| `ChatbotTelegramServiceProvider` | `CodeToUse/Voice/` (×2 retained) | Route + binding conflicts |
| Various WorkCore providers | `CodeToUse/WorkCore/` | Config key collisions |
| AI engine providers | `CodeToUse/AI/AICores/` | Entity/Engine ServiceProvider rebind risk |

These are now additionally protected by the autoload fix in Step 2: since `CodeToUse/` is no longer mapped in Composer, none of these providers can be instantiated without explicit manual import.

---

## ExtensionServiceProvider Note

`ExtensionServiceProvider` currently delegates to `ChatbotServiceProvider`. This is a dynamic extension loading mechanism. The `ExtensionServiceProvider` itself is not in `config/app.php` but is used by the marketplace/extension system. This is acceptable — extensions are loaded on-demand, not on boot.

---

## Summary

| Item | Status |
|------|--------|
| Duplicate provider registrations | None found |
| CodeToUse providers accidentally active | None — now additionally blocked by autoload fix |
| TitanRewindServiceProvider path ambiguity | Resolved by autoload fix |
| ExtensionServiceProvider dynamic loading | Safe — on-demand only |
