<?php
namespace Modules\PropertyManagement\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Modules\PropertyManagement\Entities\Property;

class PropertyOverviewController extends AccountBaseController
{
    public function show(Property $property)
    {
        $this->authorize('view', $property);
        return view('propertymanagement::properties.overview', compact('property'));
    }
}
