<?php
namespace Modules\ManagedPremises\Integrations\Core;

use Modules\ManagedPremises\Entities\PropertyVisit;

interface HrAdapterInterface
{
    /** Optionally map assigned_to to a roster/shift/attendance record. */
    public function reflectAssignment(PropertyVisit $visit): void;
}
