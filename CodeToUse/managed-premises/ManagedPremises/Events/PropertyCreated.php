<?php

namespace Modules\ManagedPremises\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\ManagedPremises\Entities\Property;

class PropertyCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Property $property) {}
}
