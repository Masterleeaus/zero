<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class WorkCoreServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            config_path('workcore.php'),
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
