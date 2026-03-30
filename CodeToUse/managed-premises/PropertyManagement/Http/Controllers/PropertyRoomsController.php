<?php
namespace Modules\PropertyManagement\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\PropertyManagement\Entities\Property;
use Modules\PropertyManagement\Entities\PropertyRoom;
use Modules\PropertyManagement\Http\Requests\StorePropertyRoomRequest;

class PropertyRoomsController extends AccountBaseController
{
    public function index(Property $property)
    {
        $this->authorize('view', $property);

        $rooms = PropertyRoom::where('company_id', company()->id)
            ->where('property_id', $property->id)
            ->orderBy('name')
            ->get();

        $storeUrl = route('propertymanagement.properties.rooms.store', $property->id);
        return view('propertymanagement::properties.rooms', compact('property','rooms','storeUrl'));
    }

    public function store(StorePropertyRoomRequest $request, Property $property)
    {
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
        $this->authorize('update', $property);
        abort_unless((int)$room->company_id === (int)company()->id, 403);

        $room->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }
}
