<?php
namespace Modules\PropertyManagement\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\PropertyManagement\Entities\Property;
use Modules\PropertyManagement\Entities\PropertyAsset;
use Modules\PropertyManagement\Http\Requests\StorePropertyAssetRequest;

class PropertyAssetsController extends AccountBaseController
{
    public function index(Property $property)
    {
        $this->authorize('view', $property);

        $assets = PropertyAsset::where('company_id', company()->id)
            ->where('property_id', $property->id)
            ->latest()
            ->get();

        $storeUrl = route('propertymanagement.properties.assets.store', $property->id);
        return view('propertymanagement::properties.assets', compact('property','assets','storeUrl'));
    }

    public function store(StorePropertyAssetRequest $request, Property $property)
    {
        $this->authorize('update', $property);

        PropertyAsset::create([
            'company_id' => company()->id,
            'property_id' => $property->id,
            'label' => $request->label,
            'category' => $request->category,
            'serial' => $request->serial,
            'location' => $request->location,
            'notes' => $request->notes,
        ]);

        return Reply::success(__('messages.recordSaved'));
    }

    public function destroy(Property $property, PropertyAsset $asset)
    {
        $this->authorize('update', $property);
        abort_unless((int)$asset->company_id === (int)company()->id, 403);

        $asset->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }
}
