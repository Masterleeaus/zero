<?php
namespace Modules\ManagedPremises\Http\Controllers;


use Modules\ManagedPremises\Http\Controllers\Concerns\EnsuresManagedPremisesPermissions;
use App\Http\Controllers\AccountBaseController;
use Modules\ManagedPremises\Entities\Property;

class PropertyOverviewController extends AccountBaseController
{
    
    use EnsuresManagedPremisesPermissions;
public function show(Property $property)
    {
        $this->ensureCanViewManagedPremises();
        $this->authorize('view', $property);
        return view('managedpremises::properties.overview', compact('property'));
    }
}
