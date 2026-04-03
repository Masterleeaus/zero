<?php

namespace Modules\TitanHello\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class MenuServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Expose navigation config to views (used by menu injection & module layouts)
        View::share('titanhelloNav', config('titanhello_navigation', []));
    }
}
