<?php
namespace Modules\ManagedPremises\Http\Controllers;


use Modules\ManagedPremises\Http\Controllers\Concerns\EnsuresManagedPremisesPermissions;
use Illuminate\Routing\Controller;
use Modules\ManagedPremises\Entities\Property;
use Modules\ManagedPremises\Entities\PropertyInspection;
use Modules\ManagedPremises\Http\Requests\StorePropertyInspectionRequest;
use Modules\ManagedPremises\Http\Requests\UpdatePropertyInspectionRequest;

class PropertyInspectionsController extends Controller
{
    
    use EnsuresManagedPremisesPermissions;
public function index(Property $property)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('view', $property);
        $inspections = PropertyInspection::company()->where('property_id', $property->id)->latest()->paginate(20);
        return view('managedpremises::inspections.index', compact('property','inspections'));
    }

    public function create(Property $property)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);
        return view('managedpremises::inspections.create', compact('property'));
    }

    public function store(StorePropertyInspectionRequest $request, Property $property)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);
        $data = $request->validated();
        $data['company_id'] = company()->id;
        $data['property_id'] = $property->id;
        PropertyInspection::create($data);
        return redirect()->route('managedpremises.properties.show', $property)->with('success', __('managedpremises::app.saved'));
    }

    public function edit(Property $property, PropertyInspection $inspection)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);
        abort_unless($inspection->property_id === $property->id, 404);
        return view('managedpremises::inspections.edit', compact('property','inspection'));
    }

    public function update(UpdatePropertyInspectionRequest $request, Property $property, PropertyInspection $inspection)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);
        abort_unless($inspection->property_id === $property->id, 404);
        $inspection->update($request->validated());
        return redirect()->route('managedpremises.properties.show', $property)->with('success', __('managedpremises::app.updated'));
    }

    public function destroy(Property $property, PropertyInspection $inspection)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);
        abort_unless($inspection->property_id === $property->id, 404);
        $inspection->delete();
        return back()->with('success', __('managedpremises::app.deleted'));
    }
}
