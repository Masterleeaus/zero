# Items (Patched Scaffold)

This module was auto-patched to include missing Worksuite/Laravel module scaffolding.

## Added
- ServiceProvider: `Modules\Items\Providers\ItemsServiceProvider`
- `module.json` with provider registration
- Routes: `routes/web.php`, `routes/api.php`
- Config: `Config/config.php`
- Controller: `Modules\Items\Http\Controllers\ItemsController`
- View: `Resources/views/index.blade.php`
- Migration placeholder
- `composer.json` (PSR-4 autoload)

## Next steps
- Flesh out migrations and models
- Wire real controllers & policies
- Add permissions and menu entries per Worksuite conventions
