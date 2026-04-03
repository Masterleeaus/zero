<?php
namespace Modules\ManagedPremises\Http\Controllers;


use Modules\ManagedPremises\Http\Controllers\Concerns\EnsuresManagedPremisesPermissions;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Modules\ManagedPremises\Entities\Property;
use Modules\ManagedPremises\Entities\PropertyHazard;
use Modules\ManagedPremises\Http\Requests\StorePropertyHazardRequest;

class PropertyHazardsController extends AccountBaseController
{
    
    use EnsuresManagedPremisesPermissions;
public function globalIndex(Request $request)
    {
        $this->ensureCanViewManagedPremises();
        $properties = Property::where('company_id', company()->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $propertyId = (int) ($request->get('property_id') ?? 0);

        $query = PropertyHazard::where('company_id', company()->id)
            ->with('property')
            ->latest();

        if ($propertyId > 0) {
            $query->where('property_id', $propertyId);
        }

        $hazards = $query->get();
        $storeUrl = route('managedpremises.hazards.store');

        return view('managedpremises::global.hazards', compact('properties', 'propertyId', 'hazards', 'storeUrl'));
    }

    public function globalStore(Request $request)
    {
        $this->ensureCanViewManagedPremises();
        $data = $request->validate([
            'property_id' => ['required', 'integer'],
            'hazard' => ['required', 'string', 'max:191'],
            'risk_level' => ['nullable', 'string', 'max:50'],
            'controls' => ['nullable', 'string'],
        ]);

        $property = Property::where('company_id', company()->id)->findOrFail($data['property_id']);
        $this->authorize('update', $property);

        PropertyHazard::create([
            'company_id' => company()->id,
            'user_id' => user()->id ?? auth()->id(),
            'property_id' => $property->id,
            'hazard' => $data['hazard'],
            'risk_level' => $data['risk_level'] ?? 'medium',
            'controls' => $data['controls'] ?? null,
        ]);

        return Reply::success(__('messages.recordSaved'));
    }

    public function index(Property $property)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('view', $property);

        $hazards = PropertyHazard::where('company_id', company()->id)
            ->where('property_id', $property->id)
            ->latest()
            ->get();

        $storeUrl = route('managedpremises.properties.hazards.store', $property->id);
        return view('managedpremises::properties.hazards', compact('property','hazards','storeUrl'));
    }

    public function store(StorePropertyHazardRequest $request, Property $property)
    {
        $this->ensureCanViewManagedPremises();
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
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);
        abort_unless((int)$hazard->company_id === (int)company()->id, 403);

        $hazard->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }
}
