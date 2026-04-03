<?php

namespace Modules\Documents\Providers;
use App\Events\NewCompanyCreatedEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Documents\Entities\Documents;
use Modules\Documents\Entities\Template;
use Modules\Documents\Listeners\CompanyCreatedListener;
use Modules\Documents\Observers\LetterObserver;
use Modules\Documents\Observers\TemplateObserver;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NewCompanyCreatedEvent::class => [CompanyCreatedListener::class],
    ];

    protected $observers = [
        Documents::class => [LetterObserver::class],
        Template::class => [TemplateObserver::class],
    ];
}
