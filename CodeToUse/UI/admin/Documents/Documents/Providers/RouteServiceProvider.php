<?php

namespace Modules\Documents\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'Modules\Documents\Http\Controllers';

    public function map(): void
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(module_path('Documents', '/Routes/web.php'));
    }
}
