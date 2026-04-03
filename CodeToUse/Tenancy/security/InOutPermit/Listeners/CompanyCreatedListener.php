<?php

namespace Modules\TrInOutPermit\Listeners;
use App\Models\ModuleSetting;
use Modules\TrInOutPermit\Entities\TrInOutPermit;

class CompanyCreatedListener
{
    public function handle($event)
    {
        $company = $event->company;
        $roles = ['admin', 'employee', 'client'];
        ModuleSetting::createRoleSettingEntry('trinoutpermit', $roles, $company);
        // TrInOutPermit::addModuleSetting($company);
    }
}
