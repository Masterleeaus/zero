<?php
namespace Modules\PropertyManagement\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\PropertyManagement\Entities\PropertyApproval;

class PropertyApprovalRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(public PropertyApproval $approval) {}
}
