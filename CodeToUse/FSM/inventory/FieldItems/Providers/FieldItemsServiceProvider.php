<?php

namespace Modules\FieldItems\Providers;

use Illuminate\Support\ServiceProvider;

class FieldItemsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'fielditems');
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        // Routes
        if (file_exists($modulePath.'/Routes/web.php')) {
            $this->loadRoutesFrom($modulePath.'/Routes/web.php');
        }
        if (file_exists($modulePath.'/Routes/api.php')) {
            $this->loadRoutesFrom($modulePath.'/Routes/api.php');
        }

        // Views
        if (is_dir($modulePath.'/Resources/views')) {
            $this->loadViewsFrom($modulePath.'/Resources/views', 'fielditems');
        }

        // Migrations
        if (is_dir($modulePath.'/Database/Migrations')) {
            $this->loadMigrationsFrom($modulePath.'/Database/Migrations');
        }

        // Translations
        if (is_dir($modulePath.'/Resources/lang')) {
            $this->loadTranslationsFrom($modulePath.'/Resources/lang', 'fielditems');
        }

        // Publish config
        if (is_file($modulePath.'/Config/config.php')) {
            $this->publishes([
                $modulePath.'/Config/config.php' => config_path('fielditems.php'),
            ], 'fielditems-config');
        }
    }
}
