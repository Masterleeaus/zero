<?php
namespace Modules\ManagedPremises\Services;

use Modules\ManagedPremises\Entities\Property;
use Modules\ManagedPremises\Entities\PropertyHazard;

class HazardService
{
    public function list(Property $property)
    {
        return PropertyHazard::where('company_id', $property->company_id)
            ->where('property_id', $property->id)
            ->orderByDesc('id')
            ->get();
    }
}
