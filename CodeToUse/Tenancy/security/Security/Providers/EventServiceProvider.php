<?php

namespace Modules\Security\Providers;

use App\Events\NewCompanyCreatedEvent;
use Modules\Security\Entities\Security;
use Modules\Security\Observers\SecurityObserver;
use Modules\Security\Listeners\CompanyCreatedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;



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
        Security::class => [SecurityObserver::class],
    ];
}
