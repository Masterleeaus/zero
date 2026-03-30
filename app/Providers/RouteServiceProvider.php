<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\Marketplace\Http\Middleware\NewExtensionInstalled;
use App\Http\Middleware\ViewSharedMiddleware;
use App\Models\Crm\Customer;
use App\Models\Crm\Enquiry;
use App\Models\Money\Invoice;
use App\Models\Money\Quote;
use App\Models\Money\Payment;
use App\Models\Work\Checklist;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/dashboard';

    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware([
                'web',  ViewSharedMiddleware::class, NewExtensionInstalled::class,
            ])->group(function () {
                require base_path('routes/web.php');
                $this->loadCoreRoutes();
            });
        });

        Route::bind('customer', static function (string|int $value) {
            return Customer::query()->whereKey($value)->firstOrFail();
        });

        Route::bind('enquiry', static function (string|int $value) {
            return Enquiry::query()->whereKey($value)->firstOrFail();
        });

        Route::bind('site', static function (string|int $value) {
            return Site::query()->whereKey($value)->firstOrFail();
        });

        Route::bind('job', static function (string|int $value) {
            return ServiceJob::query()->whereKey($value)->firstOrFail();
        });

        Route::bind('checklist', static function (string|int $value) {
            return Checklist::query()->whereKey($value)->firstOrFail();
        });

        Route::bind('quote', static function (string|int $value) {
            return Quote::query()->whereKey($value)->firstOrFail();
        });

        Route::bind('invoice', static function (string|int $value) {
            return Invoice::query()->whereKey($value)->firstOrFail();
        });

        Route::bind('payment', static function (string|int $value) {
            return Payment::query()->whereKey($value)->firstOrFail();
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', static function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    protected function loadCoreRoutes(): void
    {
        $corePath = base_path('routes/core');

        if (! is_dir($corePath)) {
            return;
        }

        $files = glob($corePath . '/*.routes.php') ?: [];

        // Enforce simple lowercase naming for core route files (e.g., crm.routes.php).
        $files = array_values(array_filter($files, static function ($file) {
            return (bool) preg_match('/^[a-z]+\.routes\.php$/', basename($file));
        }));

        sort($files);

        foreach ($files as $routeFile) {
            require $routeFile;
        }
    }
}
