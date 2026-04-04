<?php

namespace App\Providers;

use App\Services\VerticalLanguageResolver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class WorkCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(VerticalLanguageResolver::class, function () {
            return new VerticalLanguageResolver();
        });
        $this->mergeConfigFrom(base_path('config/workcore.php'), 'workcore');
        $this->mergeConfigFrom(base_path('config/verticals.php'), 'verticals');
    }

    public function boot(): void
    {
        // Register canonical FSM polymorphic morph aliases so that VehicleAssignment
        // (which stores string aliases like 'service_job') resolves correctly via Eloquent
        // morphMany/morphTo relationships without requiring full class names in the DB.
        Relation::morphMap([
            'service_job'     => \App\Models\Work\ServiceJob::class,
            'dispatch_route'  => \App\Models\Route\DispatchRoute::class,
            'shift'           => \App\Models\Work\Shift::class,
        ]);
    }
}
