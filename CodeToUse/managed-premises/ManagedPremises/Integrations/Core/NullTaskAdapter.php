<?php
namespace Modules\ManagedPremises\Integrations\Core;

use Modules\ManagedPremises\Entities\PropertyVisit;

class NullTaskAdapter implements TaskAdapterInterface
{
    public function upsertTaskForVisit(PropertyVisit $visit): ?int
    {
        return null;
    }
}
