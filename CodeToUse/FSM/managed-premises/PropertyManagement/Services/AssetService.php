<?php
namespace Modules\PropertyManagement\Services;

use Modules\PropertyManagement\Entities\Property;
use Modules\PropertyManagement\Entities\PropertyAsset;

class AssetService
{
    public function list(Property $property)
    {
        return PropertyAsset::where('company_id', $property->company_id)
            ->where('property_id', $property->id)
            ->orderByDesc('id')
            ->get();
    }
}
