<?php

declare(strict_types=1);

namespace App\Providers;

use App\Extensions\TitanRewind\System\Models\RewindCase;
use App\Domains\Marketplace\Http\Middleware\NewExtensionInstalled;
use App\Models\Work\Territory;
use App\Models\Work\Region;
use App\Models\Work\District;
use App\Models\Work\Branch;
use App\Models\Work\JobStage;
use App\Models\Work\JobType;
use App\Models\Work\JobTemplate;
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

                Route::middleware(['auth', 'throttle:120,1'])->group(function () {
                    $this->loadCoreRoutes();
                });
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

        Route::bind('case', static function (string|int $value) {
            return RewindCase::query()->whereKey($value)->firstOrFail();
        });

        Route::bind('zone', static function (string|int $value) {
            return Territory::query()->whereKey($value)->firstOrFail();
        });

        Route::bind('region', static function (string|int $value) {
            return Region::query()->whereKey($value)->firstOrFail();
        });

        Route::bind('district', static function (string|int $value) {
            return District::query()->whereKey($value)->firstOrFail();
        });

        Route::bind('branch', static function (string|int $value) {
            return Branch::query()->whereKey($value)->firstOrFail();
        });

        Route::bind('jobStage', static function (string|int $value) {
            return JobStage::query()->whereKey($value)->firstOrFail();
        });

        Route::bind('jobType', static function (string|int $value) {
            return JobType::query()->whereKey($value)->firstOrFail();
        });

        Route::bind('jobTemplate', static function (string|int $value) {
            return JobTemplate::query()->whereKey($value)->firstOrFail();
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
