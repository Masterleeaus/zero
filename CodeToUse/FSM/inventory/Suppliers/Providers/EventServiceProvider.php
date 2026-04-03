<?php

namespace Modules\Suppliers\Providers;

use App\Events\NewCompanyCreatedEvent;
use Modules\Suppliers\Entities\Supplier;
use Modules\Suppliers\Observers\SupplierObserver;
use Modules\Suppliers\Listeners\CompanyCreatedListener;
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
        Supplier::class => [SupplierObserver::class],
    ];
}
