<?php
namespace Modules\ManagedPremises\Services;

use Modules\ManagedPremises\Entities\Property;

class PropertyProfileService
{
    public function summary(Property $property): array
    {
        return [
            'property_id' => $property->id,
            'name' => $property->name,
            'address' => $property->address,
            'access_notes' => $property->access_notes,
            'hazards' => $property->hazards,
        ];
    }
}
