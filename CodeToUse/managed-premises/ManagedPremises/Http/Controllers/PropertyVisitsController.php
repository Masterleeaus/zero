<?php
namespace Modules\ManagedPremises\Http\Controllers;


use Modules\ManagedPremises\Http\Controllers\Concerns\EnsuresManagedPremisesPermissions;
use Illuminate\Routing\Controller;
use Modules\ManagedPremises\Entities\Property;
use Modules\ManagedPremises\Entities\PropertyVisit;
use Modules\ManagedPremises\Http\Requests\StorePropertyVisitRequest;
use Modules\ManagedPremises\Http\Requests\UpdatePropertyVisitRequest;

class PropertyVisitsController extends Controller
{
    
    use EnsuresManagedPremisesPermissions;
public function index(Property $property)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('view', $property);
        $visits = PropertyVisit::company()->where('property_id', $property->id)->latest()->paginate(20);
        return view('managedpremises::visits.index', compact('property','visits'));
    }

    public function create(Property $property)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);
        return view('managedpremises::visits.create', compact('property'));
    }

    public function store(StorePropertyVisitRequest $request, Property $property)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);
        $data = $request->validated();
        $data['company_id'] = company()->id;
        $data['property_id'] = $property->id;
        PropertyVisit::create($data);
        return redirect()->route('managedpremises.properties.show', $property)->with('success', __('managedpremises::app.saved'));
    }

    public function edit(Property $property, PropertyVisit $visit)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);
        abort_unless($visit->property_id === $property->id, 404);
        return view('managedpremises::visits.edit', compact('property','visit'));
    }

    public function update(UpdatePropertyVisitRequest $request, Property $property, PropertyVisit $visit)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);
        abort_unless($visit->property_id === $property->id, 404);
        $visit->update($request->validated());
        return redirect()->route('managedpremises.properties.show', $property)->with('success', __('managedpremises::app.updated'));
    }

    public function destroy(Property $property, PropertyVisit $visit)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('update', $property);
        abort_unless($visit->property_id === $property->id, 404);
        $visit->delete();
        return back()->with('success', __('managedpremises::app.deleted'));
    }
}
