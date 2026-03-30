<?php
namespace Modules\FacilityManagement\Providers;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\FacilityManagement\Entities\*;
use Modules\FacilityManagement\Policies;

class AuthServiceProvider extends ServiceProvider {
    protected $policies = [
        {Site::class} => {Policies\SitePolicy::class},\n        {Building::class} => {Policies\BuildingPolicy::class},\n        {Unit::class} => {Policies\UnitPolicy::class},\n        {UnitType::class} => {Policies\UnitTypePolicy::class},\n        {Asset::class} => {Policies\AssetPolicy::class},\n        {Inspection::class} => {Policies\InspectionPolicy::class},\n        {Doc::class} => {Policies\DocPolicy::class},\n        {Meter::class} => {Policies\MeterPolicy::class},\n        {MeterRead::class} => {Policies\MeterReadPolicy::class},\n        {Occupancy::class} => {Policies\OccupancyPolicy::class},
    ];
    public function boot(): void { $this->registerPolicies(); }
}
