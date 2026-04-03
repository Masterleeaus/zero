<?php

namespace Modules\TrWorkPermits\Listeners;
use App\Models\ModuleSetting;
use Modules\TrWorkPermits\Entities\WorkPermits;

class CompanyCreatedListener
{
    public function handle($event)
    {
        $company = $event->company;
        $roles = ['admin', 'employee', 'client'];
        ModuleSetting::createRoleSettingEntry('TrWorkPermits', $roles, $company);
        // WorkPermits::addModuleSetting($company);
    }
}
