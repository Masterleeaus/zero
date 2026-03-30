<?php
namespace Modules\ManagedPremises\Http\Controllers;


use Modules\ManagedPremises\Http\Controllers\Concerns\EnsuresManagedPremisesPermissions;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\ManagedPremises\Entities\Property;
use Modules\ManagedPremises\Entities\PropertyAsset;
use Modules\ManagedPremises\Http\Requests\StorePropertyAssetRequest;

class PropertyAssetsController extends AccountBaseController
{
    
    use EnsuresManagedPremisesPermissions;
public function index(Property $property)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('view', $property);

        $assets = PropertyAsset::where('company_id', company()->id)
            ->where('property_id', $property->id)
            ->latest()
            ->get();

        $storeUrl = route('managedpremises.properties.assets.store', $property->id);
        return view('managedpremises::properties.assets', compact('property','assets','storeUrl'));
    }

    public function store(StorePropertyAssetRequest $request, Property $property)
    {
        $this->ensureCanViewManagedPremises();
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
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);
        abort_unless((int)$asset->company_id === (int)company()->id, 403);

        $asset->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }
}
