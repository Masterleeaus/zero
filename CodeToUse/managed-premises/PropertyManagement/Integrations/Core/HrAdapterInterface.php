<?php
namespace Modules\PropertyManagement\Integrations\Core;

use Modules\PropertyManagement\Entities\PropertyVisit;

interface HrAdapterInterface
{
    /** Optionally map assigned_to to a roster/shift/attendance record. */
    public function reflectAssignment(PropertyVisit $visit): void;
}
