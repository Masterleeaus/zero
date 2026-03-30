<?php
namespace Modules\PropertyManagement\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\PropertyManagement\Entities\Property;
use Modules\PropertyManagement\Entities\PropertyVisit;
use Modules\PropertyManagement\Http\Requests\StorePropertyVisitRequest;
use Modules\PropertyManagement\Http\Requests\UpdatePropertyVisitRequest;

class PropertyVisitsController extends Controller
{
    public function index(Property $property)
    {
        $this->authorize('view', $property);
        $visits = PropertyVisit::company()->where('property_id', $property->id)->latest()->paginate(20);
        return view('propertymanagement::visits.index', compact('property','visits'));
    }

    public function create(Property $property)
    {
        $this->authorize('update', $property);
        return view('propertymanagement::visits.create', compact('property'));
    }

    public function store(StorePropertyVisitRequest $request, Property $property)
    {
        $this->authorize('update', $property);
        $data = $request->validated();
        $data['company_id'] = company()->id;
        $data['property_id'] = $property->id;
        PropertyVisit::create($data);
        return redirect()->route('propertymanagement.properties.show', $property)->with('success', __('propertymanagement::app.saved'));
    }

    public function edit(Property $property, PropertyVisit $visit)
    {
        $this->authorize('update', $property);
        abort_unless($visit->property_id === $property->id, 404);
        return view('propertymanagement::visits.edit', compact('property','visit'));
    }

    public function update(UpdatePropertyVisitRequest $request, Property $property, PropertyVisit $visit)
    {
        $this->authorize('update', $property);
        abort_unless($visit->property_id === $property->id, 404);
        $visit->update($request->validated());
        return redirect()->route('propertymanagement.properties.show', $property)->with('success', __('propertymanagement::app.updated'));
    }

    public function destroy(Property $property, PropertyVisit $visit)
    {
        $this->authorize('update', $property);
        abort_unless($visit->property_id === $property->id, 404);
        $visit->delete();
        return back()->with('success', __('propertymanagement::app.deleted'));
    }
}
