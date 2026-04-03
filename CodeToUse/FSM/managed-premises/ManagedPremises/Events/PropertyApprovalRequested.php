<?php
namespace Modules\ManagedPremises\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\ManagedPremises\Entities\PropertyApproval;

class PropertyApprovalRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(public PropertyApproval $approval) {}
}
