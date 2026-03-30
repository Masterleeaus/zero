<?php

namespace Modules\Engineerings\Providers;

use App\Events\NewCompanyCreatedEvent;
use Modules\Engineerings\Entities\WorkRequest;
use Modules\Engineerings\Observers\WorkRequestObserver;
use Modules\Engineerings\Entities\WorkItems;
use Modules\Engineerings\Observers\WorkItemsObserver;
use Modules\Engineerings\Listeners\CompanyCreatedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Engineerings\Entities\Meter;
use Modules\Engineerings\Observers\RecurringWorkOrderObserver;
use Modules\Engineerings\Entities\RecurringWorkOrder;
use Modules\Engineerings\Entities\Services;
use Modules\Engineerings\Entities\ServicesCategory;
use Modules\Engineerings\Entities\ServicesSubCategory;
use Modules\Engineerings\Entities\WorkOrder;
use Modules\Engineerings\Observers\WorkOrderObserver;
use Modules\Engineerings\Entities\WorkOrderFile;
use Modules\Engineerings\Entities\WorkServices;
use Modules\Engineerings\Observers\MeterObserver;
use Modules\Engineerings\Observers\ServicesCategoryObserver;
use Modules\Engineerings\Observers\ServicesObserver;
use Modules\Engineerings\Observers\ServicesSubCategoryObserver;
use Modules\Engineerings\Observers\WorkOrderFileObserver;
use Modules\Engineerings\Observers\WorkServicesObserver;

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
        WorkItems::class           => [WorkItemsObserver::class],
        WorkServices::class        => [WorkServicesObserver::class],
        WorkRequest::class         => [WorkRequestObserver::class],
        WorkOrder::class           => [WorkOrderObserver::class],
        WorkOrderFile::class       => [WorkOrderFileObserver::class],
        RecurringWorkOrder::class  => [RecurringWorkOrderObserver::class],
        Meter::class               => [MeterObserver::class],
        ServicesCategory::class    => [ServicesCategoryObserver::class],
        ServicesSubCategory::class => [ServicesSubCategoryObserver::class],
        Services::class            => [ServicesObserver::class],
    ];
}
