<?php
namespace Modules\ManagedPremises\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Modules\ManagedPremises\Entities\Property;
use Modules\ManagedPremises\Services\PropertyProfileService;
use Modules\ManagedPremises\Http\Controllers\Concerns\EnsuresManagedPremisesPermissions;

class PropertyProfileController extends Controller
{
    
    use EnsuresManagedPremisesPermissions;

    public function show(Property $property, PropertyProfileService $service)
    {
        $this->ensureCanViewManagedPremises();
        abort_unless(auth()->check(), 401);
        abort_unless(auth()->user()->can('managedpremises.view'), 403);

        return response()->json($service->summary($property));
    }
}