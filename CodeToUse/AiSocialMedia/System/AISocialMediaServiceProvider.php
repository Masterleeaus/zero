<?php

declare(strict_types=1);

namespace App\Extensions\AISocialMedia\System;

use App\Domains\Marketplace\Contracts\UninstallExtensionServiceProviderInterface;
use App\Extensions\AISocialMedia\System\Http\Controllers\Api\InstagramController;
use App\Extensions\AISocialMedia\System\Http\Controllers\AutomationController;
use App\Extensions\AISocialMedia\System\Http\Controllers\AutomationPlatformController;
use App\Extensions\AISocialMedia\System\Http\Controllers\AutomationSettingController;
use App\Extensions\AISocialMedia\System\Http\Controllers\AutomationStepController;
use App\Extensions\AISocialMedia\System\Http\Controllers\GenerateContentController;
use App\Extensions\AISocialMedia\System\Http\Controllers\UploadController;
use App\Extensions\AISocialMedia\System\Http\Middleware\AutomationCacheMiddleware;
use App\Http\Middleware\CheckTemplateTypeAndPlan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AISocialMediaServiceProvider extends ServiceProvider implements UninstallExtensionServiceProviderInterface
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
            ->registerCommand();

    }

    public function publishAssets(): static
    {
        $this->publishes([
            __DIR__ . '/../resources/assets/images' => public_path('vendor/ai-social-media/images'),
        ], 'extension');

        return $this;
    }

    public function registerConfig(): static
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ai-social-media.php', 'ai-social-media');

        return $this;
    }

    public function registerCommand(): static
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\GeneratePostDailyCommand::class,
                Console\Commands\GeneratePostMonthlyCommand::class,
                Console\Commands\GeneratePostWeeklyCommand::class,
            ]);

            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->command('app:generate-post-daily')->everyTwoMinutes();
                $schedule->command('app:generate-post-weekly')->everyTwoMinutes();
                $schedule->command('app:generate-post-monthly')->everyTwoMinutes();
            });
        }

        return $this;
    }

    protected function registerTranslations(): static
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'ai-social-media');

        return $this;
    }

    public function registerViews(): static
    {
        $this->loadViewsFrom([resource_path('views/default/ai-social-media')], 'ai-social-media');

        return $this;
    }

    public function registerMigrations(): static
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        return $this;
    }

    public static function uninstall(): void
    {
        setting([
            'ai_automation' => 0,
        ])->save();
    }
}
