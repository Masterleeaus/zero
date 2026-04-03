<?php

namespace Modules\TrPackage\Providers;
use App\Events\NewCompanyCreatedEvent;
use Modules\TrPackage\Listeners\CompanyCreatedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\TrPackage\Entities\Ekspedisi;
use Modules\TrPackage\Entities\Package;
use Modules\TrPackage\Entities\PackageItems;
use Modules\TrPackage\Entities\TypePackage;
use Modules\TrPackage\Observers\EkspedisiObserver;
use Modules\TrPackage\Observers\PackageItemsObserver;
use Modules\TrPackage\Observers\PackageObserver;
use Modules\TrPackage\Observers\TypePackageObserver;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NewCompanyCreatedEvent::class => [CompanyCreatedListener::class],
    ];

    protected $observers = [
        Package::class => [PackageObserver::class],
        PackageItems::class => [PackageItemsObserver::class],
        Ekspedisi::class => [EkspedisiObserver::class],
        TypePackage::class => [TypePackageObserver::class],
    ];

}
