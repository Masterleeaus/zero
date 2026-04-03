<?php
namespace Modules\ManagedPremises\Integrations\Core;

use Modules\ManagedPremises\Entities\PropertyVisit;

interface TaskAdapterInterface
{
    /** Create or update a core Task when a visit is scheduled. Return core task id or null. */
    public function upsertTaskForVisit(PropertyVisit $visit): ?int;
}
