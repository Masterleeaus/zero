<?php

namespace Extensions\TitanHello;

use Illuminate\Support\ServiceProvider;

class ExtensionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/config.php', 'titan-hello');
    }

    public function boot(): void
    {
        // Routes (admin + webhooks)
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');

        // Views
        $this->loadViewsFrom(__DIR__ . '/Views', 'titan-hello');

        // Migrations
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        // Assets
        $this->publishes([
            __DIR__ . '/Assets' => public_path('extensions/titan-hello'),
        ], 'titan-hello-assets');
    }
}
