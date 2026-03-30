<?php

namespace Modules\PropertyManagement\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\PropertyManagement\Entities\Property;
use Modules\PropertyManagement\Entities\PropertyMeterReading;
use Modules\PropertyManagement\Http\Requests\StorePropertyMeterReadingRequest;
use Modules\PropertyManagement\Services\MeterReadingService;
use Modules\PropertyManagement\Services\MeterInsightsService;

class PropertyMeterReadingsController extends AccountBaseController
{
    public function index(Property $property, MeterInsightsService $insightsSvc)
    {
        $this->authorize('view', $property);

        $readings = PropertyMeterReading::where('company_id', company()->id)
            ->where('property_id', $property->id)
            ->orderByDesc('reading_date')
            ->get();

        $units = $property->units()->orderBy('label')->get();

        $insights = $insightsSvc->insights($property);

        return view('propertymanagement::properties.meters', compact('property', 'readings', 'units', 'insights'));}

    public function store(StorePropertyMeterReadingRequest $request, Property $property, MeterReadingService $svc)
    {
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
        $this->authorize('update', $property);

        abort_unless((int) $reading->company_id === (int) company()->id, 403);
        abort_unless((int) $reading->property_id === (int) $property->id, 404);

        $reading->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }
}
