<?php

namespace Modules\FacilityManagement\Providers;

use Illuminate\Support\ServiceProvider;

class FacilityManagementServiceProvider extends ServiceProvider
    {
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'facility');
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'facility');
        $this->mergeConfigFrom(__DIR__.'/../Config/permissions.php', 'facility_permissions');
    }

    public function register(): void
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('facility.php'),
            __DIR__.'/../Config/permissions.php' => config_path('facility_permissions.php'),
        ], 'config');
    }
}
