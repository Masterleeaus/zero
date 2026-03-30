<?php

namespace Modules\Houses\Providers;

use Modules\Houses\Entities\House;
use Modules\Houses\Entities\Area;
use Modules\Houses\Entities\Tower;
use Modules\Houses\Entities\TypeHouse;
use App\Events\NewCompanyCreatedEvent;
use Modules\Houses\Observers\HouseObserver;
use Modules\Houses\Observers\AreaObserver;
use Modules\Houses\Observers\TowerObserver;
use Modules\Houses\Observers\TypeHouseObserver;
use Modules\Houses\Listeners\CompanyCreatedListener;
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
        House::class => [HouseObserver::class],
        Area::class => [AreaObserver::class],
        Tower::class => [TowerObserver::class],
        TypeHouse::class => [TypeHouseObserver::class],
    ];

}
