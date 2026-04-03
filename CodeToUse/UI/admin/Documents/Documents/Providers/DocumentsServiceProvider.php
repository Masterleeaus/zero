<?php
namespace Modules\Documents\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Documents\Console\ActivateModuleCommand;
use Modules\Documents\Console\Commands\DocumentsSnapshotAll;
use Modules\Documents\Console\Commands\DocumentsDoctorCommand;

class DocumentsServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Documents';
    protected string $moduleNameLower = 'documents';

    public function boot(): void
    {
        // Load module helpers (safe, no DB)
        require_once __DIR__ . '/../Support/helpers.php';

        // Policies (safe)
        try {
            Gate::policy(\Modules\Documents\Entities\DocumentTag::class, \Modules\Documents\Policies\DocumentTagPolicy::class);
            Gate::policy(\Modules\Documents\Entities\DocumentRequest::class, \Modules\Documents\Policies\DocumentRequestPolicy::class);
        } catch (\Throwable $e) {
            // Never break module boot
        }

        // Share link middleware (lightweight)
        try {
            if (isset($this->app['router']) && method_exists($this->app['router'], 'aliasMiddleware')) {
                $this->app['router']->aliasMiddleware('documents.tenant', \Modules\Documents\Http\Middleware\EnsureDocumentsTenant::class);
                $this->app['router']->aliasMiddleware('documents.sharelink', \Modules\Documents\Http\Middleware\VerifyDocumentShareLink::class);
            }
        } catch (\Throwable $e) {
            // Never break module boot
        }

        $this->registerCommands();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations/Documents'));

        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/web.php'));
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerCommands(): void
    {
        $this->commands([
            ActivateModuleCommand::class,
            DocumentsSnapshotAll::class,
        ]);
    }

    protected function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);
        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'Resources/lang'));
        }
    }

    protected function registerConfig(): void
    {
        $this->publishes([module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower);
    }

    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'Resources/views');
        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower . '-module-views']);
        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
        $componentNamespace = str_replace('/', '\\', config('modules.namespace') . '\\' . $this->moduleName . '\\' . config('modules.paths.generator.component-class.path'));
        Blade::componentNamespace($componentNamespace, $this->moduleNameLower);
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}