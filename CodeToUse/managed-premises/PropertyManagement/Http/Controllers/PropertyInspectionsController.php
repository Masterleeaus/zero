<?php
namespace Modules\PropertyManagement\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\PropertyManagement\Entities\Property;
use Modules\PropertyManagement\Entities\PropertyInspection;
use Modules\PropertyManagement\Http\Requests\StorePropertyInspectionRequest;
use Modules\PropertyManagement\Http\Requests\UpdatePropertyInspectionRequest;

class PropertyInspectionsController extends Controller
{
    public function index(Property $property)
    {
        $this->authorize('view', $property);
        $inspections = PropertyInspection::company()->where('property_id', $property->id)->latest()->paginate(20);
        return view('propertymanagement::inspections.index', compact('property','inspections'));
    }

    public function create(Property $property)
    {
        $this->authorize('update', $property);
        return view('propertymanagement::inspections.create', compact('property'));
    }

    public function store(StorePropertyInspectionRequest $request, Property $property)
    {
        $this->authorize('update', $property);
        $data = $request->validated();
        $data['company_id'] = company()->id;
        $data['property_id'] = $property->id;
        PropertyInspection::create($data);
        return redirect()->route('propertymanagement.properties.show', $property)->with('success', __('propertymanagement::app.saved'));
    }

    public function edit(Property $property, PropertyInspection $inspection)
    {
        $this->authorize('update', $property);
        abort_unless($inspection->property_id === $property->id, 404);
        return view('propertymanagement::inspections.edit', compact('property','inspection'));
    }

    public function update(UpdatePropertyInspectionRequest $request, Property $property, PropertyInspection $inspection)
    {
        $this->authorize('update', $property);
        abort_unless($inspection->property_id === $property->id, 404);
        $inspection->update($request->validated());
        return redirect()->route('propertymanagement.properties.show', $property)->with('success', __('propertymanagement::app.updated'));
    }

    public function destroy(Property $property, PropertyInspection $inspection)
    {
        $this->authorize('update', $property);
        abort_unless($inspection->property_id === $property->id, 404);
        $inspection->delete();
        return back()->with('success', __('propertymanagement::app.deleted'));
    }
}
