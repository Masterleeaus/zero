<?php
namespace Modules\ManagedPremises\Services;

use Modules\ManagedPremises\Entities\Property;
use Modules\ManagedPremises\Entities\PropertyAsset;

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
