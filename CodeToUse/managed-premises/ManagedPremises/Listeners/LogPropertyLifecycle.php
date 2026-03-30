<?php

namespace Modules\ManagedPremises\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\ManagedPremises\Events\PropertyCreated;
use Modules\ManagedPremises\Events\PropertyUpdated;

class LogPropertyLifecycle
{
    public function handle(PropertyCreated|PropertyUpdated $event): void
    {
        Log::info('ManagedPremises lifecycle event', [
            'event' => get_class($event),
            'property_id' => $event->property->id,
            'company_id' => $event->property->company_id,
        ]);
    }
}
