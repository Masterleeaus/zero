<?php
namespace Modules\PropertyManagement\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\PropertyManagement\Entities\PropertyVisit;

class PropertyVisitScheduled
{
    use Dispatchable, SerializesModels;

    public function __construct(public PropertyVisit $visit) {}
}
