<?php
namespace Modules\ManagedPremises\Integrations\Core;

use Modules\ManagedPremises\Entities\PropertyVisit;

class NullHrAdapter implements HrAdapterInterface
{
    public function reflectAssignment(PropertyVisit $visit): void
    {
        // no-op
    }
}
