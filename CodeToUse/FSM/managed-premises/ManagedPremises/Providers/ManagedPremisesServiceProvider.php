<?php

namespace Modules\ManagedPremises\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Modules\ManagedPremises\Support\Permissions;

class ManagedPremisesServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'ManagedPremises';
    protected string $moduleNameLower = 'managedpremises';

    public function register(): void
    {
        // Never early-return in register() (global killer prevention)
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower);

// Bind integration adapters (safe defaults)
$this->app->bind(\Modules\ManagedPremises\Integrations\Core\TaskAdapterInterface::class, \Modules\ManagedPremises\Integrations\Core\NullTaskAdapter::class);
$this->app->bind(\Modules\ManagedPremises\Integrations\Core\HrAdapterInterface::class, \Modules\ManagedPremises\Integrations\Core\NullHrAdapter::class);

    }

    public function boot(): void
    {
        // Safe early-return in boot() only after helpers/config are available
        if (function_exists('module_enabled') && !module_enabled($this->moduleNameLower)) {
            return;
        }

        $this->registerTranslations();
        $this->registerViews();
        $this->registerConfig();
                // Console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\ManagedPremises\Console\Commands\PmDoctorCommand::class,
                \Modules\ManagedPremises\Console\Commands\GenerateVisitsCommand::class,
            ]);
        }

        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        // Permissions manifest (seeders will insert into DB)
        $this->app->singleton(Permissions::class, fn () => new Permissions());

        // Sidebar include (must never throw)
        Blade::includeIf('managedpremises::sections.sidebar', 'managedpremises_sidebar');
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');

        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );
    }

    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    protected function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    protected function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths', []) as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
