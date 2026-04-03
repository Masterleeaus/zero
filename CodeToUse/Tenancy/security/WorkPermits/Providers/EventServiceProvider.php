<?php

namespace Modules\TrWorkPermits\Providers;
use App\Events\NewCompanyCreatedEvent;
use Modules\TrWorkPermits\Listeners\CompanyCreatedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\TrWorkPermits\Observers\WorkPermitsObserver;
use Modules\TrWorkPermits\Entities\WorkPermits;
use Modules\TrWorkPermits\Entities\WorkPermitsFile;
use Modules\TrWorkPermits\Observers\WorkPermitsFileObserver;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NewCompanyCreatedEvent::class => [CompanyCreatedListener::class],
    ];

    protected $observers = [
        WorkPermits::class => [WorkPermitsObserver::class],
        WorkPermitsFile::class => [WorkPermitsFileObserver::class],

    ];

}
