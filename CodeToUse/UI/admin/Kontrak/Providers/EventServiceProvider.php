<?php

namespace Modules\Kontrak\Providers;
use App\Events\NewCompanyCreatedEvent;
use Modules\Kontrak\Listeners\CompanyCreatedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Kontrak\Observers\RecurringKontrakObserver;
use Modules\Kontrak\Entities\RecurringKontrak;

class EventServiceProvider extends ServiceProvider
{
/**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        NewCompanyCreatedEvent::class => [CompanyCreatedListener::class],
    ];

    protected $observers = [
        RecurringKontrak::class => [RecurringKontrakObserver::class],
    ];
}
