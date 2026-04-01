<?php

declare(strict_types=1);

namespace App\Extensions\TitanRewind\System;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class TitanRewindServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            base_path('config/titan-rewind.php'),
            'titan-rewind'
        );

        $this->app->singleton(Services\RewindAuditService::class);
        $this->app->singleton(Services\RewindCaseService::class);
        $this->app->singleton(Services\RewindDependencyGraphService::class);
        $this->app->singleton(Services\RewindImpactAnalyzer::class);
        $this->app->singleton(Services\RewindConflictDetector::class);
        $this->app->singleton(Services\RewindRollbackProcessor::class);
        $this->app->singleton(Services\RewindFixService::class);
        $this->app->singleton(Services\RewindNotificationService::class);
        $this->app->singleton(Services\RewindHistoryService::class);
        $this->app->singleton(Services\RewindResolutionService::class);
        $this->app->singleton(Services\RewindRollbackPlannerService::class);
        $this->app->singleton(Services\RewindProcessBridgeService::class);
        $this->app->singleton(Services\RewindSignalIntegrationService::class);
        $this->app->singleton(Services\RewindExternalTransactionService::class);
        $this->app->singleton(Services\RewindReplayService::class);
        $this->app->singleton(Services\RewindSnapshotService::class);
        $this->app->singleton(Services\RewindEngine::class);
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(
            __DIR__ . '/../resources/lang',
            'titan-rewind'
        );

        $this->loadViewsFrom(
            [__DIR__ . '/../resources/views'],
            'titan-rewind'
        );

        $this->loadMigrationsFrom(
            base_path('database/migrations')
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\ProcessRewindQueue::class,
            ]);

            if (config('titan-rewind.scheduler_enabled', true)) {
                $this->app->booted(function () {
                    /** @var Schedule $schedule */
                    $schedule = $this->app->make(Schedule::class);
                    $schedule->command(
                        'titanrewind:process --limit=' . (int) config('titan-rewind.process_limit', 50)
                    )->everyFiveMinutes()->withoutOverlapping();
                });
            }
        }
    }
}
