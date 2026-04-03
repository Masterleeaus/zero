<?php

namespace Modules\TrInOutPermit\Providers;
use App\Events\NewCompanyCreatedEvent;
use Modules\TrInOutPermit\Listeners\CompanyCreatedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\TrInOutPermit\Entities\TrInOutPermit;
use Modules\TrInOutPermit\Observers\TrInOutPermitObserver;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NewCompanyCreatedEvent::class => [CompanyCreatedListener::class],
    ];

    protected $observers = [
        TrInOutPermit::class => [TrInOutPermitObserver::class],
    ];

}
