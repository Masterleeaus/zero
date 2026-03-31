# Social Suite Merge (SocialMedia + AiSocialMedia + SocialMediaAgent)

## Files merged
- Added `routes/core/social.routes.php` to register all SocialMedia, AiSocialMedia, and SocialMediaAgent web/API routes via the core route loader.
- Copied module views into `resources/views/default/social-media`, `resources/views/default/ai-social-media`, and `resources/views/default/social-media-agent` to align with the themed view resolver.
- Enabled PSR-4 autoloading for `App\Extensions\*` from `CodeToUse/` via `composer.json` so extension classes are discoverable without duplicating core providers.

## Files adapted
- Adjusted service providers:
  - `CodeToUse/SocialMedia/System/SocialMediaServiceProvider.php`
  - `CodeToUse/AiSocialMedia/System/AISocialMediaServiceProvider.php`
  - `CodeToUse/SocialMediaAgent/System/SocialMediaAgentServiceProvider.php`
  - Providers now rely on the core route loader, point views to `resources/views/default/*`, and retain command/config/translation/migration wiring.

## Files skipped
- Database migrations were left untouched (tables already exist per host instructions).
- Platform-specific assets and configs remain in-place under `CodeToUse/*/resources` for future publish steps.

## Infrastructure stripped
- Removed legacy in-provider route wiring in favor of the core `routes/core/*.routes.php` loader to avoid duplicate route registration.
- Removed temporary `__MACOSX` artifact folders from extracted extension packages.

## Conflicts resolved
- Avoided duplicate route registration by centralizing all extension routes in `routes/core/social.routes.php`.
- Prevented view resolver conflicts by loading extension views from the `resources/views/default` theme path.

## Rename-later targets
- Keep current naming for posts/campaigns/platforms/accounts/analytics/calendar per explicit non-goal; rename pass deferred.
