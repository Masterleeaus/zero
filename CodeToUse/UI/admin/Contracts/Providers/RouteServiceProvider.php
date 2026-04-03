<?php

namespace Modules\Contracts\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function map(): void
    {
        Route::middleware('web')->group(module_path('Contracts', 'Routes/web.php'));
        Route::prefix('api')->middleware('api')->group(module_path('Contracts', 'Routes/api.php'));
    }
}
