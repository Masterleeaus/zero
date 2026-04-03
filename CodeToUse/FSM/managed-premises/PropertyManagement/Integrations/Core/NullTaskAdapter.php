<?php
namespace Modules\PropertyManagement\Integrations\Core;

use Modules\PropertyManagement\Entities\PropertyVisit;

class NullTaskAdapter implements TaskAdapterInterface
{
    public function upsertTaskForVisit(PropertyVisit $visit): ?int
    {
        return null;
    }
}
