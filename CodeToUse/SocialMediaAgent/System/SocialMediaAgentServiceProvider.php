<?php

declare(strict_types=1);

namespace App\Extensions\SocialMediaAgent\System;

use App\Domains\Marketplace\Contracts\ExtensionRegisterKeyProviderInterface;
use App\Domains\Marketplace\Contracts\UninstallExtensionServiceProviderInterface;
use App\Extensions\SocialMediaAgent\System\Console\Commands\CheckPendingImagesCommand;
use App\Extensions\SocialMediaAgent\System\Console\Commands\GenerateAgentPostsCommand;
use App\Extensions\SocialMediaAgent\System\Console\Commands\PostMetricsAnalyzerCommand;
use App\Extensions\SocialMediaAgent\System\Console\Commands\PostPerformanceAdvisorCommand;
use App\Extensions\SocialMediaAgent\System\Console\Commands\SeedDemoDataCommand;
use App\Extensions\SocialMediaAgent\System\Console\Commands\TrendFinderCommand;
use App\Extensions\SocialMediaAgent\System\Console\Commands\UpdateAverageMetricsCommand;
use App\Extensions\SocialMediaAgent\System\Console\Commands\WeeklySocialTrendsCommand;
use App\Extensions\SocialMediaAgent\System\Models\SocialMediaAgent;
use App\Extensions\SocialMediaAgent\System\Policies\SocialMediaAgentPolicy;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class SocialMediaAgentServiceProvider extends ServiceProvider implements ExtensionRegisterKeyProviderInterface, UninstallExtensionServiceProviderInterface
{
    public function register(): void {}

    public function boot(Kernel $kernel): void
    {
        $this->registerTranslations()
            ->registerViews()
            ->registerMigrations()
            ->registerPolicies()
            ->registerCommands()
            ->publishAssets();
    }

    protected function registerCommands(): static
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateAgentPostsCommand::class,
                CheckPendingImagesCommand::class,
                PostMetricsAnalyzerCommand::class,
                TrendFinderCommand::class,
                PostPerformanceAdvisorCommand::class,
                WeeklySocialTrendsCommand::class,
                SeedDemoDataCommand::class,
                UpdateAverageMetricsCommand::class,
            ]);

            // exec('php artisan social-media-agent:generate-posts 1');
            // Schedule tasks
            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->command('social-media-agent:generate-posts')->everyTwoMinutes();
                $schedule->command('social-media-agent:check-pending-images')->everyFiveMinutes();
                $schedule->command('social-media-agent:post-metrics-analyzer')->cron('10 1 */3 * *');
                $schedule->command('social-media-agent:post-performance-advisor')->cron('25 1 */3 * *');
                $schedule->command('social-media-agent:trend-finder')->cron('40 1 */3 * *');
                $schedule->command('social-media-agent:weekly-social-trends')->cron('0 2 */3 * *');
            });
        }

        return $this;
    }

    protected function registerPolicies(): static
    {
        Gate::policy(SocialMediaAgent::class, SocialMediaAgentPolicy::class);

        return $this;
    }

    public function publishAssets(): static
    {
        $this->publishes([
            __DIR__ . '/../resources/assets/images' => public_path('vendor/social-media-agent/images'),
            __DIR__ . '/../resources/assets/videos' => public_path('vendor/social-media-agent/videos'),
            __DIR__ . '/../resources/assets/images' => public_path('vendor/business-agent/images'),
            __DIR__ . '/../resources/assets/videos' => public_path('vendor/business-agent/videos'),
        ], 'extension');

        return $this;
    }

    protected function registerTranslations(): static
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', $this->registerKey());
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'business-agent');

        return $this;
    }

    public function registerViews(): static
    {
        $this->loadViewsFrom(
            [resource_path('views/default/panel/user/business-suite/social-media-agent')],
            $this->registerKey()
        );
        $this->loadViewsFrom(
            [resource_path('views/default/panel/user/business-suite/social-media-agent')],
            'business-agent'
        );

        return $this;
    }

    public function registerMigrations(): static
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        return $this;
    }

    public static function uninstall(): void {}

    public function registerKey(): string
    {
        return 'social-media-agent';
    }
}
