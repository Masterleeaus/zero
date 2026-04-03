# EXTENSION_BOUNDARY_AUDIT.md

**Phase 8 — Step 7: Extension Boundary Audit**
**Date:** 2026-04-03
**Scope:** app/Extensions, CodeToUse/Extensions, ExtensionLibrary modules

---

## 1. Active Extensions (app/Extensions)

Currently one extension is in production in `app/Extensions/`:

### TitanRewind (`app/Extensions/TitanRewind/`)

| Attribute | Value |
|-----------|-------|
| Provider | `App\Extensions\TitanRewind\System\TitanRewindServiceProvider` |
| Registered In | `config/app.php` (explicit) |
| Models | `RewindFix`, `RewindEvent`, `RewindLink`, `RewindCase`, `RewindSnapshot`, `RewindConflict` |
| Tables | `titan_rewind_fixes`, `titan_rewind_events`, `tz_rewind_links`, `titan_rewind_cases`, `tz_rewind_snapshots`, `tz_rewind_conflicts` |
| Routes | `routes/core/rewind.routes.php` (glob-discovered) |
| Controllers | `TitanRewindCaseController`, `TitanRewindApiController` |
| Middleware | None registered globally |

**Boundary Check:**
- Does NOT write into core tables ✅
- Does NOT register global middleware ✅
- `tz_rewind_snapshots` table is also created in `2026_03_31_000100_add_federation_metadata_and_tables.php` — **DUPLICATE TABLE CREATION** (see Migration Collision Map)

**Status: LOW risk — clean boundary. One migration collision to resolve.**

---

## 2. CodeToUse/Extensions — ExtensionLibrary Catalogue

The `CodeToUse/Extensions/ExtensionLibrary/` directory contains **~30+ extension packages**, none currently active. Full list:

```
AIChatPro, AdvancedImage, AiChatProFileChat, AiChatProFolders, AiChatProMemory,
AiImagePro, AiProductShot, AiRealtimeImage, AiVideoPro, AiWriterTemplates,
Announcement, Canvas, ChatProTempChat, ChatSetting, ChatShare, ChatbotAgent,
ChatbotMessenger, ChatbotTelegram, ChatbotVoice, ChatbotWhatsapp, CheckoutRegistration,
CloudflareR2, ContentManager, CreativeSuite, DiscountManager, ElevenlabsVoiceChat,
External-Chatbot, FluxPro, FocusMode, Hubspot, Introductions, LiveCustomizer,
MailchimpNewsletter, Maintenance, MarketingBot, MegaMenu, Menu, Migration, MultiModel,
NanoBanana, Newsletter, OnboardingPro, OpenRouter, OpenaiRealtimeChat, Plagiarism,
ProductPhotography, SeoTool, VoiceIsolator, Webchat, Wordpress, Xero, cryptomus
```

---

## 3. Extension Boundary Violations (CodeToUse — If Activated)

### 3a. Extensions Writing Into Core Tables

| Extension | Core Table Risk | Detail |
|-----------|----------------|--------|
| `CheckoutRegistration` | `users` table | Adds `reg_sub_status` column via migration |
| `AiChatProFolders` | `user_openai` table | Adds `folder_id` column via migration |
| `LiveCustomizer` | `menus` table | `2024_05_08_163635_menu_migrate.php` modifies menu schema |
| `Menu` / `MegaMenu` | `menus` table | Likely alters core menu structure |

### 3b. Duplicate Extension Slugs

The following extension names appear in BOTH `CodeToUse/Extensions/ExtensionLibrary/` AND `CodeToUse/Voice/` passes, indicating slug duplication:

| Extension | In ExtensionLibrary | In Voice Passes | Conflict |
|-----------|-------------------|----------------|---------|
| `ChatbotWhatsapp` | ✅ | ✅ (9 copies) | **HIGH** |
| `ChatbotVoice` | ✅ | ✅ (9 copies) | **HIGH** |
| `ElevenlabsVoiceChat` | ✅ | ✅ (9 copies) | **HIGH** |
| `ChatbotMessenger` | ✅ | ✅ (3 copies) | **HIGH** |
| `ChatbotTelegram` | ✅ | ✅ (3 copies) | **HIGH** |

**If any of these are registered via the Marketplace, the slug collision will prevent multiple versions from being installed simultaneously.**

### 3c. Duplicate Extension Providers

The following provider classes appear in both `CodeToUse/Extensions/ExtensionLibrary/` AND `CodeToUse/Voice/` bundles:

| Provider Class | ExtensionLibrary | Voice Pass Count |
|---------------|-----------------|-----------------|
| `ChatbotWhatsappServiceProvider` | ✅ | 9 copies |
| `ChatbotVoiceServiceProvider` | ✅ | 9 copies |
| `ElevenLabsVoiceChatServiceProvider` | ✅ | 9 copies |
| `ChatbotMessengerServiceProvider` | ✅ | 3 copies |
| `ChatbotTelegramServiceProvider` | ✅ | 3 copies |

**If the Marketplace auto-discovers and registers providers from `CodeToUse/`, duplicate provider registration is guaranteed.**

### 3d. Extensions Registering Global Middleware

Extensions that may register global middleware (from patterns in CodeToUse):

| Extension | Risk |
|-----------|------|
| `CheckoutRegistration` | `RegistrationServiceProvider` may inject middleware into the auth pipeline |
| `Maintenance` | Likely registers global maintenance-mode middleware |
| `External-Chatbot` | May register webhook/embed middleware |

### 3e. Extensions Overriding `routes/web.php`

No extension has been detected directly modifying `routes/web.php`. However:
- Extensions with their own `routes/web.php` file (in their bundle directory) would replace the host `routes/web.php` if incorrectly merged.
- `CodeToUse/WorkCore/WorkCore/titancore/routes/` contains route files including a `web.php` — **overwrite risk if integrated without path isolation**.

---

## 4. Extension Model Table Usage

TitanRewind extension uses isolated tables prefixed with `titan_rewind_*` and `tz_rewind_*`. This is a clean pattern.

**Recommended pattern for all future extension integrations:**
- Use `tz_<extension>_*` table prefix
- Never alter core tables from extension migrations

---

## 5. Summary Table

| Risk Level | Finding |
|------------|---------|
| **CRITICAL** | 5 extension slugs exist in both ExtensionLibrary and Voice passes — will collide on registration |
| **CRITICAL** | 5 extension provider classes duplicated 9× in Voice passes — must select ONE canonical version |
| **HIGH** | `CheckoutRegistration`, `AiChatProFolders`, `LiveCustomizer` write into core tables |
| **HIGH** | `CodeToUse/WorkCore/titancore/routes/web.php` could overwrite host routing if merged naively |
| **MEDIUM** | `Maintenance` and `External-Chatbot` may register global middleware affecting all requests |
| **LOW** | `TitanRewind` (active) has clean boundaries — only `tz_rewind_snapshots` migration duplication needs resolution |
