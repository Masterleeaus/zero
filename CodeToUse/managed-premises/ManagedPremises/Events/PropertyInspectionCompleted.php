<?php
namespace Modules\ManagedPremises\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\ManagedPremises\Entities\PropertyInspection;

class PropertyInspectionCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public PropertyInspection $inspection) {}
}
