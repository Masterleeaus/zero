## Business Agent / Business Suite rename pass (in-progress)

### Scope covered in this pass
- Added business-facing aliases for extension registration slugs so `business-suite` and `business-agent` resolve to the existing providers.
- Added business-facing blade/translation/config namespaces for the legacy Social Media and Social Media Agent providers to allow immediate adoption without breaking existing references.
- Published asset aliases under `vendor/business-suite` and `vendor/business-agent` for UI compatibility.
- Captured initial rename map (see `business_agent_full_rename_map.md`) to drive the full refactor.

### Files touched
- `app/Domains/Marketplace/MarketplaceServiceProvider.php` — new aliases `business-suite` and `business-agent` pointing to existing providers.
- `CodeToUse/SocialMedia/System/SocialMediaServiceProvider.php` — added business-suite config, translation, view, and asset aliases.
- `CodeToUse/SocialMediaAgent/System/SocialMediaAgentServiceProvider.php` — added business-agent translation, view, and asset aliases.
- `docs/merge_reports/business_agent_full_rename_map.md` — initial rename inventory.

### Namespaces / routes / views
- Blade namespaces now expose both `social-media::` and `business-suite::`.
- Blade namespaces now expose both `social-media-agent::` and `business-agent::`.
- Extension slugs expose both the legacy and business names; route names remain legacy and need follow-up.

### Migrations / models / tables
- No database renames performed in this pass. Mapping captured for tables/models that must be converted (posts → drafts, campaigns → programs, platforms/accounts → channels/contacts).

### Aliases preserved
- Legacy slugs `social-media` and `social-media-agent` remain active.
- Legacy namespaces remain available; business aliases added to support gradual migration.

### Deferred / remaining work
- Full route rename and URI adjustment to `dashboard.user.business-suite.*` and `dashboard.user.business-agent.*` (with compatibility aliases).
- Class/namespace renames for all `SocialMedia*` and `SocialMediaAgent*` classes to the business equivalents.
- Blade include path refactor to move folders away from `social-media` naming.
- Config key, translation key, and menu/event/analytics label renames to business terminology.
- Model/table renames and data-safe migrations (posts → drafts, campaigns → programs, platforms/accounts → channels/contacts, analytics → insights).
- Semantic updates so backend logic operates on business concepts (draft/program/channel/insight) instead of social media wording.
- Comprehensive re-scan to eliminate residual social-media terminology outside documented compatibility shims.

### Residual legacy terms
- Numerous `social-media` / `social_media` / `SocialMedia` occurrences remain across routes, controllers, models, views, translations, and tests. They are intentionally left for the planned full rename with compatibility handling.
