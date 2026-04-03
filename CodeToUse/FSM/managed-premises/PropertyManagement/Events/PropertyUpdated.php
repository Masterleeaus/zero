<?php

namespace Modules\PropertyManagement\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\PropertyManagement\Entities\Property;

class PropertyUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Property $property) {}
}
