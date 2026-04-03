<?php

namespace Modules\PropertyManagement\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = 'Modules\PropertyManagement\Http\Controllers';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapWebRoutes();
        $this->mapApiRoutes();
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('PropertyManagement', '/Routes/web.php'));
    }

    protected function mapApiRoutes(): void
    {
        if (!file_exists(module_path('PropertyManagement', 'Routes/api.php'))) {
            return;
        }

        Route::middleware('api')
            ->prefix('api')
            ->namespace($this->moduleNamespace)
            ->group(module_path('PropertyManagement', 'Routes/api.php'));
    }
}
