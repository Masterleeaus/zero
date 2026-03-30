<?php
namespace Modules\PropertyManagement\Integrations\Core;

use Modules\PropertyManagement\Entities\PropertyVisit;

class NullHrAdapter implements HrAdapterInterface
{
    public function reflectAssignment(PropertyVisit $visit): void
    {
        // no-op
    }
}
