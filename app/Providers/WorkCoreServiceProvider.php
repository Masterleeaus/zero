<?php

namespace App\Providers;

use App\Services\VerticalLanguageResolver;
use Illuminate\Support\ServiceProvider;

class WorkCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(VerticalLanguageResolver::class, function () {
            return new VerticalLanguageResolver();
        });
        $this->mergeConfigFrom(base_path('config/workcore.php'), 'workcore');
        $this->mergeConfigFrom(base_path('config/verticals.php'), 'verticals');
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/workcore.php',
            'workcore'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
