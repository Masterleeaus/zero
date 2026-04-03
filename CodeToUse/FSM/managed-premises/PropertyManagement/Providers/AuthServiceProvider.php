<?php

namespace Modules\PropertyManagement\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use Modules\PropertyManagement\Entities\Property;
use Modules\PropertyManagement\Entities\PropertyVisit;
use Modules\PropertyManagement\Entities\PropertyInspection;
use Modules\PropertyManagement\Entities\PropertyServicePlan;
use Modules\PropertyManagement\Entities\PropertyMeterReading;

use Modules\PropertyManagement\Policies\PropertyPolicy;
use Modules\PropertyManagement\Policies\PropertyVisitPolicy;
use Modules\PropertyManagement\Policies\PropertyInspectionPolicy;
use Modules\PropertyManagement\Policies\PropertyServicePlanPolicy;
use Modules\PropertyManagement\Policies\PropertyMeterReadingPolicy;

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
