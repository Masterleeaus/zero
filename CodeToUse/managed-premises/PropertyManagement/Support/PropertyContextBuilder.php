<?php

namespace Modules\PropertyManagement\Support;

use Modules\PropertyManagement\Entities\Property;

class PropertyContextBuilder
{
    public static function forProperty(Property $property): array
    {
        return [
            'module' => 'propertymanagement',
            'page' => 'property.show',
            'company_id' => $property->company_id,
            'property_id' => $property->id,
            'property' => [
                'name' => $property->name,
                'address' => $property->address,
                'access_notes' => $property->access_notes,
                'hazards' => $property->hazards,
            ],
        ];
    }
}
