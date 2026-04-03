<?php

namespace Modules\ManagedPremises\Http\Controllers;


use Modules\ManagedPremises\Http\Controllers\Concerns\EnsuresManagedPremisesPermissions;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\ManagedPremises\Entities\Property;
use Modules\ManagedPremises\Entities\PropertyUnit;
use Illuminate\Http\Request;

class PropertyUnitsController extends AccountBaseController
{
    
    use EnsuresManagedPremisesPermissions;
public function index($propertyId)
    {
        $this->ensureCanViewManagedPremises();
        $viewPermission = user()->permission('managedpremises.units.view');
        abort_403(!in_array($viewPermission, ['all', 'owned', 'both']));

        $this->property = Property::with('units')->findOrFail($propertyId);
        return view('managedpremises::properties.units', $this->data);
    }

    public function store(Request $request, $propertyId)
    {
        $this->ensureCanViewManagedPremises();
        $editPermission = user()->permission('managedpremises.units.create');
        abort_403(!in_array($editPermission, ['all']));

        $validated = $request->validate([
            'unit_code' => ['nullable', 'string', 'max:100'],
            'unit_name' => ['required', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:100'],
            'tower' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:100'],
            'area' => ['nullable', 'numeric'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $property = Property::findOrFail($propertyId);
        $property->units()->create($validated);

        return Reply::success(__('messages.recordSaved'));
    }

    public function destroy($propertyId, $unitId)
    {
        $this->ensureCanViewManagedPremises();
        $editPermission = user()->permission('managedpremises.units.create');
        abort_403(!in_array($editPermission, ['all']));

        $unit = PropertyUnit::where('property_id', $propertyId)->findOrFail($unitId);
        $unit->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }
}
