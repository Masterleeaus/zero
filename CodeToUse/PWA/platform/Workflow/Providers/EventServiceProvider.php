<?php

namespace Modules\Workflow\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Workflow\Listeners\OnDomainEventListener;
use Modules\Workflow\Listeners\OnEloquentEventListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * We intentionally do not hard-bind to specific domain events here.
     * Instead we subscribe to:
     *  - a small set of canonical domain-event prefixes (via manual dispatch from modules)
     *  - system-wide Eloquent lifecycle events (created/updated/deleted)
     *
     * This keeps Workflow compatible with the whole system without duplicating module logic.
     */
    public function boot(): void
    {
        parent::boot();

        // System-wide Eloquent lifecycle triggers (tenant-safe evaluation occurs in engine).
        Event::listen('eloquent.created: *', [OnEloquentEventListener::class, 'handleCreated']);
        Event::listen('eloquent.updated: *', [OnEloquentEventListener::class, 'handleUpdated']);
        Event::listen('eloquent.deleted: *', [OnEloquentEventListener::class, 'handleDeleted']);

        // Generic domain events (modules may dispatch: workflow.event('inspection.completed', $payload))
        Event::listen('workflow.domain.*', [OnDomainEventListener::class, 'handle']);
    }
}
