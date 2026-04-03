<?php

namespace Modules\Parking\Listeners;
use App\Models\ModuleSetting;
use Modules\Parking\Entities\Parking;

class CompanyCreatedListener
{
    public function handle($event)
    {
        $company = $event->company;
        $roles = ['admin', 'employee'];
        ModuleSetting::createRoleSettingEntry('parking', $roles, $company);
        // Parking::addModuleSetting($company);
    }
}
