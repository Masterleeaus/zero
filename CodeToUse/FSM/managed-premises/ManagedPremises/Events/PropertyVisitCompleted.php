<?php
namespace Modules\ManagedPremises\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\ManagedPremises\Entities\PropertyVisit;

class PropertyVisitCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public PropertyVisit $visit) {}
}
