<?php

namespace Modules\PropertyManagement\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\PropertyManagement\Events\PropertyCreated;
use Modules\PropertyManagement\Events\PropertyUpdated;

class LogPropertyLifecycle
{
    public function handle(PropertyCreated|PropertyUpdated $event): void
    {
        Log::info('PropertyManagement lifecycle event', [
            'event' => get_class($event),
            'property_id' => $event->property->id,
            'company_id' => $event->property->company_id,
        ]);
    }
}
