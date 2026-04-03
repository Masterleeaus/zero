<?php

namespace Modules\TitanTalk\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class MenuServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Expose navigation config to views (used by menu injection & module layouts)
        View::share('titantalkNav', config('titantalk-navigation', []));
    }
}
