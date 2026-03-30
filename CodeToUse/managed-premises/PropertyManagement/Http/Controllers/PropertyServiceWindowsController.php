<?php
namespace Modules\PropertyManagement\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\PropertyManagement\Entities\Property;
use Modules\PropertyManagement\Entities\PropertyServiceWindow;
use Modules\PropertyManagement\Http\Requests\StorePropertyServiceWindowRequest;

class PropertyServiceWindowsController extends AccountBaseController
{
    public function index(Property $property)
    {
        $this->authorize('view', $property);

        $windows = PropertyServiceWindow::where('company_id', company()->id)
            ->where('property_id', $property->id)
            ->latest()
            ->get();

        $storeUrl = route('propertymanagement.properties.servicewindows.store', $property->id);
        return view('propertymanagement::properties.service-windows', compact('property','windows','storeUrl'));
    }

    public function store(StorePropertyServiceWindowRequest $request, Property $property)
    {
        $this->authorize('update', $property);

        PropertyServiceWindow::create([
            'company_id' => company()->id,
            'property_id' => $property->id,
            'days' => $request->days,
            'time_from' => $request->time_from,
            'time_to' => $request->time_to,
            'notes' => $request->notes,
        ]);

        return Reply::success(__('messages.recordSaved'));
    }

    public function destroy(Property $property, PropertyServiceWindow $window)
    {
        $this->authorize('update', $property);
        abort_unless((int)$window->company_id === (int)company()->id, 403);

        $window->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }
}
