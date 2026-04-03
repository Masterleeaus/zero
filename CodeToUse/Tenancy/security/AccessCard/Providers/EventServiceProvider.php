<?php

namespace Modules\TrAccessCard\Providers;
use App\Events\NewCompanyCreatedEvent;
use Modules\TrAccessCard\Listeners\CompanyCreatedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\TrAccessCard\Entities\TrAccessCard;
use Modules\TrAccessCard\Entities\CardItems;
use Modules\TrAccessCard\Observers\CardItemsObserver;
use Modules\TrAccessCard\Observers\CardObserver;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NewCompanyCreatedEvent::class => [CompanyCreatedListener::class],
    ];

    protected $observers = [
        TrAccessCard::class => [CardObserver::class],
        CardItems::class => [CardItemsObserver::class],
    ];

}
