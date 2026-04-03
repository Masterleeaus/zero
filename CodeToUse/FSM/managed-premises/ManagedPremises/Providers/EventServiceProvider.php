<?php

namespace Modules\ManagedPremises\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
                \Modules\ManagedPremises\Events\PropertyVisitCreated::class => [
            \Modules\ManagedPremises\Listeners\LogPropertyEvent::class,
        ],
        \Modules\ManagedPremises\Events\PropertyInspectionCompleted::class => [
            \Modules\ManagedPremises\Listeners\LogPropertyEvent::class,
            \Modules\ManagedPremises\Listeners\NotifyPropertyContacts::class,
        ],
        \Modules\ManagedPremises\Events\PropertyDocumentUploaded::class => [
            \Modules\ManagedPremises\Listeners\LogPropertyEvent::class,
        ],
        \Modules\ManagedPremises\Events\PropertyApprovalRequested::class => [
            \Modules\ManagedPremises\Listeners\LogPropertyEvent::class,
            \Modules\ManagedPremises\Listeners\NotifyPropertyContacts::class,
        ],
// Register module events here if needed.
    ];
}
