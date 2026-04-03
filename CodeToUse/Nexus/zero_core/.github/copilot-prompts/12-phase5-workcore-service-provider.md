# Copilot Task: Phase 5 — WorkCoreServiceProvider Registration

## Context
WorkCore merge Phase 5. The WorkCoreServiceProvider was prepared in the overlay but never registered in the MagicAI host. Provider snippets from the pre-merge audit also need carrying across.

## Your Task

### 1. Create the real WorkCoreServiceProvider
Create `app/Providers/WorkCoreServiceProvider.php`:

```php
<?php

namespace App\Providers;

use App\Services\VerticalLanguageResolver;
use Illuminate\Support\ServiceProvider;

class WorkCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind VerticalLanguageResolver as singleton using workcore vertical config
        $this->app->singleton(VerticalLanguageResolver::class, function ($app) {
            $vertical = config('workcore.vertical', 'cleaning');
            $config   = config('verticals', []);
            return new VerticalLanguageResolver($vertical, $config);
        });

        // Merge workcore config
        $this->mergeConfigFrom(config_path('workcore.php'), 'workcore');
    }

    public function boot(): void
    {
        // Feature gate helper — check if a workcore feature is enabled
        if (! function_exists('workcore_feature')) {
            function workcore_feature(string $feature): bool {
                return (bool) config("workcore.features.{$feature}", false);
            }
        }
    }
}
```

### 2. Register in config/app.php
In `config/app.php`, add to the `providers` array under Application Service Providers:
```php
App\Providers\WorkCoreServiceProvider::class,
```

### 3. Carry over provider snippets from pre-merge audit
Check `WorkCore.zip → MAGICAI_PREMERGE/15_PROVIDER_SNIPPETS.md`. Ensure these are present in `app/Providers/AppServiceProvider.php`:

a) **Cashier ignores migrations** — in `register()`:
```php
\Laravel\Cashier\Cashier::ignoreMigrations();
```

b) **CarbonInterval formatHuman macro** — in `boot()`:
```php
\Carbon\CarbonInterval::macro('formatHuman', function () {
    return $this->cascade()->forHumans();
});
```

c) **HTTPS redirect in production** — in `boot()`:
```php
if (app()->isProduction()) {
    \Illuminate\Support\Facades\URL::forceScheme('https');
}
```

Check if each is already present before adding. Do NOT duplicate.

### 4. Create output doc
Create `docs/PROVIDER_BINDING_MAP.md`:
- List all providers registered (host + workcore)
- Document what WorkCoreServiceProvider binds
- Note any conflicts resolved

## Constraints
- Run `php artisan config:cache` after changes
- Run `php artisan optimize:clear` to flush provider cache
- Confirm `php artisan tinker --execute="app(App\Services\VerticalLanguageResolver::class)->label('sites');"` returns `'Jobs'`
