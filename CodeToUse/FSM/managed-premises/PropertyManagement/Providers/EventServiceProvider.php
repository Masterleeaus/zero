<?php

namespace Modules\PropertyManagement\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
                \Modules\PropertyManagement\Events\PropertyVisitCreated::class => [
            \Modules\PropertyManagement\Listeners\LogPropertyEvent::class,
        ],
        \Modules\PropertyManagement\Events\PropertyInspectionCompleted::class => [
            \Modules\PropertyManagement\Listeners\LogPropertyEvent::class,
            \Modules\PropertyManagement\Listeners\NotifyPropertyContacts::class,
        ],
        \Modules\PropertyManagement\Events\PropertyDocumentUploaded::class => [
            \Modules\PropertyManagement\Listeners\LogPropertyEvent::class,
        ],
        \Modules\PropertyManagement\Events\PropertyApprovalRequested::class => [
            \Modules\PropertyManagement\Listeners\LogPropertyEvent::class,
            \Modules\PropertyManagement\Listeners\NotifyPropertyContacts::class,
        ],
// Register module events here if needed.
    ];
}
