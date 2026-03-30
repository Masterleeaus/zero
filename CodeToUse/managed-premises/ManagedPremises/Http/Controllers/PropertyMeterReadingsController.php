<?php

namespace Modules\ManagedPremises\Http\Controllers;


use Modules\ManagedPremises\Http\Controllers\Concerns\EnsuresManagedPremisesPermissions;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\ManagedPremises\Entities\Property;
use Modules\ManagedPremises\Entities\PropertyMeterReading;
use Modules\ManagedPremises\Http\Requests\StorePropertyMeterReadingRequest;
use Modules\ManagedPremises\Services\MeterReadingService;
use Modules\ManagedPremises\Services\MeterInsightsService;

class PropertyMeterReadingsController extends AccountBaseController
{
    
    use EnsuresManagedPremisesPermissions;
public function index(Property $property, MeterInsightsService $insightsSvc)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('view', $property);

        $readings = PropertyMeterReading::where('company_id', company()->id)
            ->where('property_id', $property->id)
            ->orderByDesc('reading_date')
            ->get();

        $units = $property->units()->orderBy('label')->get();

        $insights = $insightsSvc->insights($property);

        return view('managedpremises::properties.meters', compact('property', 'readings', 'units', 'insights'));}

    public function store(StorePropertyMeterReadingRequest $request, Property $property, MeterReadingService $svc)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);

        $payload = $request->validated();

        $payload['company_id'] = company()->id;
        $payload['property_id'] = $property->id;
        $payload['created_by'] = user()->id;

        $payload = $svc->calculate($payload);

        PropertyMeterReading::create($payload);

        return Reply::success(__('messages.recordSaved'));
    }

    public function destroy(Property $property, PropertyMeterReading $reading)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);

        abort_unless((int) $reading->company_id === (int) company()->id, 403);
        abort_unless((int) $reading->property_id === (int) $property->id, 404);

        $reading->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }
}
