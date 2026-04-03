<?php

namespace Modules\TitanHello\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->routes(function () {
            $base = __DIR__ . '/../Routes';

            $web = $base . '/web.php';
            if (file_exists($web)) {
                Route::middleware('web')
                    ->namespace('Modules\TitanHello\Http\Controllers')
                    ->group($web);
            }

            $api = $base . '/api.php';
            if (file_exists($api)) {
                Route::middleware('api')
                    ->prefix('api')
                    ->namespace('Modules\TitanHello\Http\Controllers')
                    ->group($api);
            }
        });
    }
}
