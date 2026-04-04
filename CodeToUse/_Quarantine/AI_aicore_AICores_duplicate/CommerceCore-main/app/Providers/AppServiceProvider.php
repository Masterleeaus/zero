<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Blade::directive('money', function ($amount) {
            return '<?php echo app("App\Services\CurrencyService")->format(' . $amount . '); ?>';
        });

        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $view->with('currency', app(\App\Services\CurrencyService::class));
        });
    }
}
