<?php

namespace Modules\TitanTalk\Providers;

use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/permissions.php', 'titantalk-permissions');
        $this->mergeConfigFrom(__DIR__ . '/../Config/navigation.php', 'titantalk-navigation');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../Config/permissions.php' => config_path('titantalk-permissions.php'),
            __DIR__ . '/../Config/navigation.php' => config_path('titantalk-navigation.php'),
        ], 'titantalk-config');
    }
}
