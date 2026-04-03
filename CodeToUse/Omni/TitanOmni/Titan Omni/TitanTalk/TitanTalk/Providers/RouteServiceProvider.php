<?php

namespace Modules\TitanTalk\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Laravel 10+ (incl. Laravel 12) expects RouteServiceProviders to register
     * routes via the routes() callback rather than relying on a map() method.
     *
     * If routes are not registered, named routes like `titantalk.dashboard`
     * will not exist, which also prevents the sidebar/menu injection from
     * ever linking to the module.
     */
    public function boot(): void
    {
        $this->routes(function () {
            $base = __DIR__ . '/../Routes';

            // Web routes
            $webFiles = [
                $base . '/web.php',
                $base . '/aiconverse.php',
            ];

            // Optional web patches (kept in-module so core files don't need edits)
            foreach (glob($base . '/patches/*.php') ?: [] as $p) {
                $name = basename($p);
                if (Str::startsWith($name, 'web.')) {
                    $webFiles[] = $p;
                }
            }

            foreach ($webFiles as $file) {
                if (file_exists($file)) {
                    Route::middleware('web')
                        ->namespace('Modules\\TitanTalk\\Http\\Controllers')
                        ->group($file);
                }
            }

            // API routes
            $apiFiles = [
                $base . '/api.php',
            ];

            foreach (glob($base . '/patches/*.php') ?: [] as $p) {
                $name = basename($p);
                // api.* are API-only; p* are "mixed" patches we intentionally load in the API context
                // (any web-only routes from p* must be moved to web.* patches to avoid /api prefixing).
                if (Str::startsWith($name, 'api.') || Str::startsWith($name, 'p')) {
                    $apiFiles[] = $p;
                }
            }

            foreach ($apiFiles as $file) {
                if (file_exists($file)) {
                    Route::middleware('api')
                        ->prefix('api')
                        ->namespace('Modules\\TitanTalk\\Http\\Controllers')
                        ->group($file);
                }
            }
        });
    }
}
