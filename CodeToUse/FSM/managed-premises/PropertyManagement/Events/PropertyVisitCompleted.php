<?php
namespace Modules\PropertyManagement\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\PropertyManagement\Entities\PropertyVisit;

class PropertyVisitCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public PropertyVisit $visit) {}
}
