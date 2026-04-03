<?php
namespace Modules\FixedAssets\Providers;
use Illuminate\Support\ServiceProvider;
class FixedAssetsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'FixedAssets');
        if (file_exists(__DIR__.'/../Config/config.php')) {
            $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'fixedassets');
        }
    }
}
