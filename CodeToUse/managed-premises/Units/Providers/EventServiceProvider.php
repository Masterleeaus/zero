<?php

namespace Modules\Units\Providers;

use Modules\Units\Entities\Unit;
use Modules\Units\Entities\Floor;
use Modules\Units\Entities\Tower;
use Modules\Units\Entities\TypeUnit;
use App\Events\NewCompanyCreatedEvent;
use Modules\Units\Observers\UnitObserver;
use Modules\Units\Observers\FloorObserver;
use Modules\Units\Observers\TowerObserver;
use Modules\Units\Observers\TypeUnitObserver;
use Modules\Units\Listeners\CompanyCreatedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Units\Entities\UsersUnit;
use Modules\Units\Observers\UnitConfigurationObserver;

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
        Unit::class => [UnitObserver::class],
        Floor::class => [FloorObserver::class],
        Tower::class => [TowerObserver::class],
        TypeUnit::class => [TypeUnitObserver::class],
        UsersUnit::class => [UnitConfigurationObserver::class],
    ];
}
