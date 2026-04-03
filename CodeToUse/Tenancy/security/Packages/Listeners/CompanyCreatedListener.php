<?php

namespace Modules\TrPackage\Listeners;
use App\Models\ModuleSetting;

class CompanyCreatedListener
{
    public function handle($event)
    {
        $company = $event->company;
        $roles = ['admin', 'employee'];
        ModuleSetting::createRoleSettingEntry('trpackage', $roles, $company);
        // trpackage::addModuleSetting($company);
    }
}
