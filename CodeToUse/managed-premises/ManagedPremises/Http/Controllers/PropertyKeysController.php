<?php

namespace Modules\ManagedPremises\Http\Controllers;


use Modules\ManagedPremises\Http\Controllers\Concerns\EnsuresManagedPremisesPermissions;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Modules\ManagedPremises\Entities\Property;
use Modules\ManagedPremises\Entities\PropertyKey;
use Modules\ManagedPremises\Http\Requests\StorePropertyKeyRequest;

class PropertyKeysController extends AccountBaseController
{
    
    use EnsuresManagedPremisesPermissions;
public function globalIndex(Request $request)
    {
        $this->ensureCanViewManagedPremises();
        $properties = Property::where('company_id', company()->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $propertyId = (int) ($request->get('property_id') ?? 0);

        $query = PropertyKey::where('company_id', company()->id)
            ->with('property')
            ->latest();

        if ($propertyId > 0) {
            $query->where('property_id', $propertyId);
        }

        $keys = $query->get();
        $storeUrl = route('managedpremises.keys.store');

        return view('managedpremises::global.keys', compact('properties', 'propertyId', 'keys', 'storeUrl'));
    }

    public function globalStore(Request $request)
    {
        $this->ensureCanViewManagedPremises();
        $data = $request->validate([
            'property_id' => ['required', 'integer'],
            'type' => ['required', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:191'],
            'code' => ['nullable', 'string', 'max:191'],
            'notes' => ['nullable', 'string'],
        ]);

        $property = Property::where('company_id', company()->id)->findOrFail($data['property_id']);
        $this->authorize('update', $property);

        PropertyKey::create([
            'company_id' => company()->id,
            'user_id' => user()->id ?? auth()->id(),
            'property_id' => $property->id,
            'type' => $data['type'],
            'location' => $data['location'] ?? null,
            'code' => $data['code'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return Reply::success(__('messages.recordSaved'));
    }
    public function index(Property $property)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('view', $property);

        $keys = PropertyKey::where('company_id', company()->id)
            ->where('property_id', $property->id)
            ->latest()
            ->get();

        return view('managedpremises::properties.keys', compact('property', 'keys'));
    }

    public function store(StorePropertyKeyRequest $request, Property $property)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);

        PropertyKey::create([
            'company_id' => company()->id,
            'property_id' => $property->id,
            'type' => $request->type,
            'location' => $request->location,
            'code' => $request->code,
            'notes' => $request->notes,
        ]);

        return Reply::success(__('messages.recordSaved'));
    }

    public function destroy(Property $property, PropertyKey $key)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);

        if ((int)$key->company_id !== (int)company()->id) {
            abort(403);
        }

        $key->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }
}
