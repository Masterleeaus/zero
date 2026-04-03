<?php
namespace Modules\ManagedPremises\Support\Exports;

use Modules\ManagedPremises\Entities\Property;

class PropertyCsvExporter
{
    public function toArray(Property $property): array
    {
        return [
            'id' => $property->id,
            'name' => $property->name,
            'address' => $property->address,
            'access_notes' => $property->access_notes,
            'hazards' => $property->hazards,
        ];
    }
}
