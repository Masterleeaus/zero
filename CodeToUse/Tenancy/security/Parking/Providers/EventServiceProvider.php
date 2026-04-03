<?php

namespace Modules\Parking\Providers;
use App\Events\NewCompanyCreatedEvent;
use Modules\Parking\Listeners\CompanyCreatedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Parking\Entities\Parking;
use Modules\Parking\Entities\ParkingItems;
use Modules\Parking\Observers\ParkingItemsObserver;
use Modules\Parking\Observers\ParkingObserver;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NewCompanyCreatedEvent::class => [CompanyCreatedListener::class],
    ];

    protected $observers = [
        Parking::class => [ParkingObserver::class],
        ParkingItems::class => [ParkingItemsObserver::class],
    ];

}
