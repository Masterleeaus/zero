<?php
namespace Modules\PropertyManagement\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\PropertyManagement\Entities\PropertyInspection;

class PropertyInspectionCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public PropertyInspection $inspection) {}
}
