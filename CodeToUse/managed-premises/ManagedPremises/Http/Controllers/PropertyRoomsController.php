<?php
namespace Modules\ManagedPremises\Http\Controllers;


use Modules\ManagedPremises\Http\Controllers\Concerns\EnsuresManagedPremisesPermissions;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Modules\ManagedPremises\Entities\Property;
use Modules\ManagedPremises\Entities\PropertyRoom;
use Modules\ManagedPremises\Http\Requests\StorePropertyRoomRequest;

class PropertyRoomsController extends AccountBaseController
{
    
    use EnsuresManagedPremisesPermissions;
/**
     * Sidebar-friendly global list of rooms (optionally filtered by site).
     */
    public function globalIndex(Request $request)
    {
        $this->ensureCanViewManagedPremises();
        $properties = Property::where('company_id', company()->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $propertyId = (int) ($request->get('property_id') ?? 0);

        $query = PropertyRoom::where('company_id', company()->id)
            ->with('property')
            ->orderBy('name');

        if ($propertyId > 0) {
            $query->where('property_id', $propertyId);
        }

        $rooms = $query->get();

        $storeUrl = route('managedpremises.rooms.store');
        return view('managedpremises::global.rooms', compact('properties', 'propertyId', 'rooms', 'storeUrl'));
    }

    /**
     * Create a room from the global list page.
     */
    public function globalStore(Request $request)
    {
        $this->ensureCanViewManagedPremises();
        $data = $request->validate([
            'property_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:191'],
            'type' => ['nullable', 'string', 'max:191'],
            'notes' => ['nullable', 'string'],
        ]);

        $property = Property::where('company_id', company()->id)->findOrFail($data['property_id']);
        $this->authorize('update', $property);

        PropertyRoom::create([
            'company_id' => company()->id,
            'user_id' => user()->id ?? auth()->id(),
            'property_id' => $property->id,
            'name' => $data['name'],
            'type' => $data['type'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return Reply::success(__('messages.recordSaved'));
    }

    public function index(Property $property)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('view', $property);

        $rooms = PropertyRoom::where('company_id', company()->id)
            ->where('property_id', $property->id)
            ->orderBy('name')
            ->get();

        $storeUrl = route('managedpremises.properties.rooms.store', $property->id);
        return view('managedpremises::properties.rooms', compact('property','rooms','storeUrl'));
    }

    public function store(StorePropertyRoomRequest $request, Property $property)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);

        PropertyRoom::create([
            'company_id' => company()->id,
            'property_id' => $property->id,
            'name' => $request->name,
            'type' => $request->type,
            'notes' => $request->notes,
        ]);

        return Reply::success(__('messages.recordSaved'));
    }

    public function destroy(Property $property, PropertyRoom $room)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);
        abort_unless((int)$room->company_id === (int)company()->id, 403);

        $room->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }
}
