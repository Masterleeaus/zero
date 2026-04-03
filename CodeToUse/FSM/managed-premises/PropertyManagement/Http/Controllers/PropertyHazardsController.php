<?php
namespace Modules\PropertyManagement\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\PropertyManagement\Entities\Property;
use Modules\PropertyManagement\Entities\PropertyHazard;
use Modules\PropertyManagement\Http\Requests\StorePropertyHazardRequest;

class PropertyHazardsController extends AccountBaseController
{
    public function index(Property $property)
    {
        $this->authorize('view', $property);

        $hazards = PropertyHazard::where('company_id', company()->id)
            ->where('property_id', $property->id)
            ->latest()
            ->get();

        $storeUrl = route('propertymanagement.properties.hazards.store', $property->id);
        return view('propertymanagement::properties.hazards', compact('property','hazards','storeUrl'));
    }

    public function store(StorePropertyHazardRequest $request, Property $property)
    {
        $this->authorize('update', $property);

        PropertyHazard::create([
            'company_id' => company()->id,
            'property_id' => $property->id,
            'hazard' => $request->hazard,
            'risk_level' => $request->risk_level ?? 'medium',
            'controls' => $request->controls,
        ]);

        return Reply::success(__('messages.recordSaved'));
    }

    public function destroy(Property $property, PropertyHazard $hazard)
    {
        $this->authorize('update', $property);
        abort_unless((int)$hazard->company_id === (int)company()->id, 403);

        $hazard->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }
}
