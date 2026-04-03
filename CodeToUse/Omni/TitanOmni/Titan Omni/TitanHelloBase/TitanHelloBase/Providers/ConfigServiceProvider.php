<?php

namespace Modules\TitanHello\Providers;

use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'titanhello');
        $this->mergeConfigFrom(__DIR__ . '/../Config/navigation.php', 'titanhello_navigation');
        $this->mergeConfigFrom(__DIR__ . '/../Config/permissions.php', 'titanhello_permissions');
    }
}
