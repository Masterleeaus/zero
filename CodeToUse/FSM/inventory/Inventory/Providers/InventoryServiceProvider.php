<?php

namespace Modules\Inventory\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'inventory');
    }

    public function boot(): void
    {
        // Register policies
        \Illuminate\Support\Facades\Gate::policy(\Modules\Inventory\Entities\Item::class, \Modules\Inventory\Policies\ItemPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\Modules\Inventory\Entities\StockMovement::class, \Modules\Inventory\Policies\StockMovementPolicy::class);

        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->app['router']->aliasMiddleware('inventory.tenant', \Modules\Inventory\Http\Middleware\WithTenant::class);
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'inventory');

        // Publish config
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('inventory.php'),
        ], 'inventory-config');

        // Permissions sample (adjust to donor's roles)
        Gate::define('inventory.view', fn($user) => method_exists($user, 'hasPermissionTo') ? $user->hasPermissionTo('inventory.view') : true);
        Gate::define('inventory.manage', fn($user) => method_exists($user, 'hasPermissionTo') ? $user->hasPermissionTo('inventory.manage') : false);
    }
}
