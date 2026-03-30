<?php
namespace Modules\ManagedPremises\Http\Controllers;


use Modules\ManagedPremises\Http\Controllers\Concerns\EnsuresManagedPremisesPermissions;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\ManagedPremises\Entities\Property;
use Modules\ManagedPremises\Entities\PropertyServiceWindow;
use Modules\ManagedPremises\Http\Requests\StorePropertyServiceWindowRequest;

class PropertyServiceWindowsController extends AccountBaseController
{
    
    use EnsuresManagedPremisesPermissions;
public function index(Property $property)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('view', $property);

        $windows = PropertyServiceWindow::where('company_id', company()->id)
            ->where('property_id', $property->id)
            ->latest()
            ->get();

        $storeUrl = route('managedpremises.properties.servicewindows.store', $property->id);
        return view('managedpremises::properties.service-windows', compact('property','windows','storeUrl'));
    }

    public function store(StorePropertyServiceWindowRequest $request, Property $property)
    {
        $this->ensureCanViewManagedPremises();
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
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);
        abort_unless((int)$window->company_id === (int)company()->id, 403);

        $window->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }
}
