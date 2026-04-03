# Vertical language resolver

Added overlay files for MagicAI import:
- `magicai_overlay/config/verticals.php`
- `magicai_overlay/app/Services/VerticalLanguageResolver.php`
- `magicai_overlay/app/Support/workcore_helpers.php`
- updated `magicai_overlay/app/Providers/WorkCoreServiceProvider.php`

Purpose:
- keep database + backend logic stable
- swap surface vocabulary by vertical
- default to `cleaning` for MVP
- allow future vertical packs without destructive rewrites
