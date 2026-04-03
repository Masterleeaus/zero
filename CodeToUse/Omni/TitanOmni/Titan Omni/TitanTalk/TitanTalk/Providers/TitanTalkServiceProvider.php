<?php

namespace Modules\TitanTalk\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Route;
use Modules\TitanTalk\Http\Middleware\InjectTitanTalkMenu;

class TitanTalkServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge config if present
        $configPath = __DIR__ . '/../Config/config.php';
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'titantalk');
        }

        // Register RouteServiceProvider if present
        if (class_exists(\Modules\TitanTalk\Providers\RouteServiceProvider::class)) {
            $this->app->register(RouteServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $moduleBase = dirname(__DIR__);

        // Add our menu injection middleware to the 'web' group
        try { $this->app['router']->pushMiddlewareToGroup('web', InjectTitanTalkMenu::class); } catch (\Throwable $e) {}

        // Views
        $views = $moduleBase . '/Resources/views';
        if (is_dir($views)) {
            $this->loadViewsFrom($views, 'titantalk');
            $this->publishes([
                $views => resource_path('views/vendor/aiconverse'),
            ], 'aiconverse-views');
        }

        // Translations
        $lang = $moduleBase . '/Resources/lang';
        if (is_dir($lang)) {
            $this->loadTranslationsFrom($lang, 'titantalk');
            $this->publishes([
                $lang => resource_path('lang/vendor/aiconverse'),
            ], 'aiconverse-lang');
        }

        // Migrations
        $migrations = $moduleBase . '/Database/Migrations';
        if (is_dir($migrations)) {
            $this->loadMigrationsFrom($migrations);
        }

        // Factories (only in non-production)
        if ($this->app->runningInConsole() && class_exists(Factory::class)) {
            $factories = $moduleBase . '/Database/factories';
            if (is_dir($factories)) {
                app(Factory::class)->load($factories);
            }
        }

        // Publish config
        $configPath = $moduleBase . '/Config/config.php';
        if (file_exists($configPath)) {
            $this->publishes([
                $configPath => config_path('titantalk.php'),
            ], 'aiconverse-config');
        }
    }

    public function provides(): array
    {
        return [];
    }
}
