<?php

declare(strict_types=1);

namespace App\Extensions\SocialMedia\System;

use App\Domains\Marketplace\Contracts\UninstallExtensionServiceProviderInterface;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

class SocialMediaServiceProvider extends ServiceProvider implements UninstallExtensionServiceProviderInterface
{
    public function register(): void
    {

        $this->registerConfig();
    }

    public function boot(Kernel $kernel): void
    {
        $this->registerTranslations()
            ->registerViews()
            ->registerMigrations()
            ->publishAssets()
            ->registerComponents()
            ->registerCommand();

    }

    public function registerCommand(): static
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\PublishedCommand::class,
                Console\Commands\XRefreshTokenCommand::class,
                Console\Commands\XPostMetricsCommand::class,
                Console\Commands\FacebookPostMetricsCommand::class,
                Console\Commands\InstagramPostMetricsCommand::class,
            ]);

            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->command('app:social-media-published-command')->everyTwoMinutes();
                $schedule->command('app:social-media-x-refresh')->everyThreeMinutes();
                $schedule->command('app:social-media-facebook-post-metrics')->everyThreeMinutes();
                $schedule->command('app:social-media-instagram-post-metrics')->everyThreeMinutes();
            });
        }

        return $this;
    }

    public function registerComponents(): static
    {
        //        $this->loadViewComponentsAs('example', []);

        return $this;
    }

    public function publishAssets(): static
    {
        $this->publishes([
            __DIR__ . '/../resources/assets' => public_path('vendor/social-media'),
        ], 'extension');

        return $this;
    }

    public function registerConfig(): static
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/social-media.php', 'social-media');

        return $this;
    }

    protected function registerTranslations(): static
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'social-media');

        return $this;
    }

    public function registerViews(): static
    {
        $this->loadViewsFrom(
            [resource_path('views/default/panel/user/business-suite/social-media')],
            'social-media'
        );

        return $this;
    }

    public function registerMigrations(): static
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        return $this;
    }

    public static function uninstall(): void
    {
        // TODO: Implement uninstall() method.
    }
}
