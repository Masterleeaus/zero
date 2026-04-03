<?php

namespace Modules\PropertyManagement\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\PropertyManagement\Entities\Property;
use Modules\PropertyManagement\Entities\PropertyKey;
use Modules\PropertyManagement\Http\Requests\StorePropertyKeyRequest;

class PropertyKeysController extends AccountBaseController
{
    public function index(Property $property)
    {
        $this->authorize('view', $property);

        $keys = PropertyKey::where('company_id', company()->id)
            ->where('property_id', $property->id)
            ->latest()
            ->get();

        return view('propertymanagement::properties.keys', compact('property', 'keys'));
    }

    public function store(StorePropertyKeyRequest $request, Property $property)
    {
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
        $this->authorize('update', $property);

        if ((int)$key->company_id !== (int)company()->id) {
            abort(403);
        }

        $key->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }
}
