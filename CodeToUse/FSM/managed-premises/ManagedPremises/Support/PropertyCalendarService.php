<?php
namespace Modules\ManagedPremises\Support;

use Modules\ManagedPremises\Entities\PropertyServicePlan;
use Modules\ManagedPremises\Entities\PropertyVisit;

class PropertyCalendarService
{
    public function createVisitFromPlan(PropertyServicePlan $plan, array $overrides = []): PropertyVisit
    {
        return PropertyVisit::create(array_merge([
            'company_id' => $plan->company_id,
            'property_id' => $plan->property_id,
            'service_plan_id' => $plan->id,
            'visit_type' => $plan->service_type,
            'status' => 'scheduled',
        ], $overrides));
    }
}
