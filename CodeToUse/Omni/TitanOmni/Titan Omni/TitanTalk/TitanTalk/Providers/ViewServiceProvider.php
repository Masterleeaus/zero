<?php

namespace Modules\TitanTalk\Providers;

use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Keep module views namespaced and predictable.
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'titantalk');
    }
}
