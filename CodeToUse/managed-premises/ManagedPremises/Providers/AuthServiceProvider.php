<?php

namespace Modules\ManagedPremises\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use Modules\ManagedPremises\Entities\Property;
use Modules\ManagedPremises\Entities\PropertyVisit;
use Modules\ManagedPremises\Entities\PropertyInspection;
use Modules\ManagedPremises\Entities\PropertyServicePlan;
use Modules\ManagedPremises\Entities\PropertyMeterReading;

use Modules\ManagedPremises\Policies\PropertyPolicy;
use Modules\ManagedPremises\Policies\PropertyVisitPolicy;
use Modules\ManagedPremises\Policies\PropertyInspectionPolicy;
use Modules\ManagedPremises\Policies\PropertyServicePlanPolicy;
use Modules\ManagedPremises\Policies\PropertyMeterReadingPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Property::class => PropertyPolicy::class,
        PropertyVisit::class => PropertyVisitPolicy::class,
        PropertyInspection::class => PropertyInspectionPolicy::class,
        PropertyServicePlan::class => PropertyServicePlanPolicy::class,
        PropertyMeterReading::class => PropertyMeterReadingPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
