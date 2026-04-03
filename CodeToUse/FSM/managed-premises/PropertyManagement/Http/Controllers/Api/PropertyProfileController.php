<?php
namespace Modules\PropertyManagement\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Modules\PropertyManagement\Entities\Property;
use Modules\PropertyManagement\Services\PropertyProfileService;

class PropertyProfileController extends Controller
{
    public function show(Property $property, PropertyProfileService $service)
    {
        abort_unless(auth()->check(), 401);
        abort_unless(auth()->user()->can('propertymanagement.view'), 403);

        return response()->json($service->summary($property));
    }
}
