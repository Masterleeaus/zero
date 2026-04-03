# Provider Binding Map

Generated for Phase 5 WorkCore merge. Documents all service providers registered in `config/app.php` and the bindings introduced by `WorkCoreServiceProvider`.

---

## Registered Providers

### Laravel Framework Providers
| Provider | Purpose |
|----------|---------|
| `Illuminate\Auth\AuthServiceProvider` | Authentication |
| `Illuminate\Broadcasting\BroadcastServiceProvider` | Broadcasting |
| `Illuminate\Bus\BusServiceProvider` | Command bus |
| `Illuminate\Cache\CacheServiceProvider` | Cache |
| `Illuminate\Foundation\Providers\ConsoleSupportServiceProvider` | Artisan CLI |
| `Illuminate\Cookie\CookieServiceProvider` | Cookies |
| `Illuminate\Database\DatabaseServiceProvider` | Database |
| `Illuminate\Encryption\EncryptionServiceProvider` | Encryption |
| `Illuminate\Filesystem\FilesystemServiceProvider` | Filesystem |
| `Illuminate\Foundation\Providers\FoundationServiceProvider` | Foundation |
| `Illuminate\Hashing\HashServiceProvider` | Hashing |
| `Illuminate\Mail\MailServiceProvider` | Mail |
| `Illuminate\Notifications\NotificationServiceProvider` | Notifications |
| `Illuminate\Pagination\PaginationServiceProvider` | Pagination |
| `Illuminate\Pipeline\PipelineServiceProvider` | Pipeline |
| `Illuminate\Queue\QueueServiceProvider` | Queues |
| `Illuminate\Redis\RedisServiceProvider` | Redis |
| `Illuminate\Auth\Passwords\PasswordResetServiceProvider` | Password reset |
| `Illuminate\Session\SessionServiceProvider` | Sessions |
| `Illuminate\Translation\TranslationServiceProvider` | Translations |
| `Illuminate\Validation\ValidationServiceProvider` | Validation |
| `Illuminate\View\ViewServiceProvider` | Views |

### Package Providers
| Provider | Purpose |
|----------|---------|
| `Spatie\Permission\PermissionServiceProvider` | Role/permission management |
| `Elseyyid\LaravelJsonLocationsManager\Providers\LaravelJsonLocationsManagerServiceProvider` | JSON localisation |
| `Barryvdh\DomPDF\ServiceProvider` | PDF generation |
| `Igaster\LaravelTheme\themeServiceProvider` | Theme switching |

### Application Providers
| Provider | Purpose |
|----------|---------|
| `App\Providers\AppServiceProvider` | Core app bootstrap (DB, config, health, macros, observers) |
| `App\Providers\WorkCoreServiceProvider` | WorkCore domain bindings (see below) |
| `App\Providers\AuthServiceProvider` | Auth gates and policies |
| `App\Providers\BroadcastServiceProvider` | Broadcast channel auth |
| `App\Providers\EventServiceProvider` | Event→listener map |
| `App\Providers\RouteServiceProvider` | Route loading and model binding |
| `App\Providers\ViewServiceProvider` | View composers and shared data |
| `App\Providers\MacrosServiceProvider` | Reusable collection/string macros |
| `App\Providers\AwsServiceProvider` | AWS SDK configuration |
| `App\Domains\Entity\EntityServiceProvider` | Entity domain |
| `App\Domains\Engine\EngineServiceProvider` | AI engine domain |
| `App\Providers\TitanSignalsServiceProvider` | TitanSignals process state machine |
| `App\Extensions\TitanRewind\System\TitanRewindServiceProvider` | TitanRewind audit extension |
| `App\Providers\TitanCoreServiceProvider` | TitanCore AI router + kernel |
| `App\Providers\TitanPwaServiceProvider` | PWA manifest |
| `App\Domains\Marketplace\MarketplaceServiceProvider` | Marketplace/add-on aliases |

---

## WorkCoreServiceProvider Bindings

**File:** `app/Providers/WorkCoreServiceProvider.php`

### Singleton Bindings
| Abstract | Concrete | Notes |
|----------|----------|-------|
| `App\Services\VerticalLanguageResolver` | `App\Services\VerticalLanguageResolver` | Reads `config('workcore.vertical')` and `config('verticals.*')` |

### Config Merges
| Config key | Source file |
|------------|------------|
| `workcore` | `config/workcore.php` |
| `verticals` | `config/verticals.php` |

### `VerticalLanguageResolver` Usage
```php
app(App\Services\VerticalLanguageResolver::class)->label('sites'); // → 'Jobs'
app(App\Services\VerticalLanguageResolver::class)->label('site');  // → 'Job'
app(App\Services\VerticalLanguageResolver::class)->vertical();     // → 'cleaning' (default)
app(App\Services\VerticalLanguageResolver::class)->feature('crm'); // → true/false
```

---

## AppServiceProvider Snippets

The following snippets were confirmed present in `app/Providers/AppServiceProvider.php` as part of Phase 5:

| Snippet | Method | Status |
|---------|--------|--------|
| `\Laravel\Cashier\Cashier::ignoreMigrations()` | `register()` | ✅ Added Phase 5 |
| `URL::forceScheme('https')` in production | `forceSchemeHttps()` → `boot()` | ✅ Already present |
| `CarbonInterval::macro('formatHuman', ...)` | `bootCarbonMacros()` → `boot()` | ✅ Already present |

---

## Conflicts Resolved

| Conflict | Resolution |
|----------|-----------|
| `WorkCoreServiceProvider` registered twice in `config/app.php` | Duplicate removed; single registration retained after `AppServiceProvider` |

---

## Deferred / Out of Scope

- Rename pass: `workcore_feature()` helper is defined in `WorkCoreServiceProvider::boot()` (prompt spec) but currently lives in `app/Helpers/helpers.php` — consolidation deferred to a later rename pass.
- Cashier migration table conflicts (if any) should be validated after `php artisan migrate --pretend`.
