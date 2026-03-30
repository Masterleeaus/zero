<?php
namespace Modules\PropertyManagement\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\PropertyManagement\Entities\Property;
use Modules\PropertyManagement\Entities\PropertyServicePlan;
use Modules\PropertyManagement\Http\Requests\StorePropertyServicePlanRequest;
use Modules\PropertyManagement\Http\Requests\UpdatePropertyServicePlanRequest;

class PropertyServicePlansController extends Controller
{
    public function index(Property $property)
    {
        $this->authorize('view', $property);
        $plans = PropertyServicePlan::company()->where('property_id', $property->id)->latest()->paginate(20);
        return view('propertymanagement::service_plans.index', compact('property','plans'));
    }

    public function create(Property $property)
    {
        $this->authorize('update', $property);
        return view('propertymanagement::service_plans.create', compact('property'));
    }

    public function store(StorePropertyServicePlanRequest $request, Property $property)
    {
        $this->authorize('update', $property);
        $data = $request->validated();
        $data['company_id'] = company()->id;
        $data['property_id'] = $property->id;
        PropertyServicePlan::create($data);
        return redirect()->route('propertymanagement.properties.show', $property)->with('success', __('propertymanagement::app.saved'));
    }

    public function edit(Property $property, PropertyServicePlan $plan)
    {
        $this->authorize('update', $property);
        abort_unless($plan->property_id === $property->id, 404);
        return view('propertymanagement::service_plans.edit', compact('property','plan'));
    }

    public function update(UpdatePropertyServicePlanRequest $request, Property $property, PropertyServicePlan $plan)
    {
        $this->authorize('update', $property);
        abort_unless($plan->property_id === $property->id, 404);
        $plan->update($request->validated());
        return redirect()->route('propertymanagement.properties.show', $property)->with('success', __('propertymanagement::app.updated'));
    }

    public function destroy(Property $property, PropertyServicePlan $plan)
    {
        $this->authorize('update', $property);
        abort_unless($plan->property_id === $property->id, 404);
        $plan->delete();
        return back()->with('success', __('propertymanagement::app.deleted'));
    }
}
