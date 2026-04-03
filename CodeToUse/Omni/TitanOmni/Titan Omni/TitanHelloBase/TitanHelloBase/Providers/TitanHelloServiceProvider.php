<?php

namespace Modules\TitanHello\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Modules\TitanHello\Http\Middleware\InjectTitanHelloMenu;
use Modules\TitanHello\Http\Middleware\EnsureTenantContext;
use Modules\TitanHello\Http\Middleware\EnforceTitanHelloPermissions;
use Modules\TitanHello\Console\Commands\PruneRecordingsCommand;

class TitanHelloServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // NOTE:
        // - Routes are registered by Modules\TitanHello\Providers\RouteServiceProvider
        // - Views are registered by Modules\TitanHello\Providers\ViewServiceProvider
        // This provider is responsible for module-level runtime wiring that WorkSuite
        // does not do automatically (e.g. safe sidebar injection without core edits).

        /** @var Router $router */
        $router = $this->app['router'];

        if ($this->app->runningInConsole()) {
            $this->commands([
                PruneRecordingsCommand::class,
            ]);
        }

        // Register + apply the menu injector middleware so the module shows in the
        // WorkSuite sidebar without modifying core views.
        $router->aliasMiddleware('titanhello.menu', InjectTitanHelloMenu::class);
        $router->pushMiddlewareToGroup('web', InjectTitanHelloMenu::class);

        // Useful aliases for routes inside this module.
        $router->aliasMiddleware('titanhello.tenant', EnsureTenantContext::class);
        $router->aliasMiddleware('titanhello.perm', EnforceTitanHelloPermissions::class);
    }
}
