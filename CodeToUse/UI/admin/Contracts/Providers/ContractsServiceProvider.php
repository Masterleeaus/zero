<?php

namespace Modules\Contracts\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class ContractsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/contracts.php', 'contracts');
        $this->app->register(RouteServiceProvider::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'contracts');

        // Auto-run menu seeder on module:seed
        if ($this->app->runningInConsole()) {
            $this->callAfterResolving('seeder', function ($seeder) {
                $seeder->call(\Modules\Contracts\Database\Seeders\MenuSeeder::class);
            });
        }
    }
}
