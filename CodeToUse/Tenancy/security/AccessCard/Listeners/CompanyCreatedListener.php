<?php

namespace Modules\TrAccessCard\Listeners;
use App\Models\ModuleSetting;
use Modules\TrAccessCard\Entities\TrAccessCard;

class CompanyCreatedListener
{
    public function handle($event)
    {
        $company = $event->company;
        $roles = ['admin', 'employee', 'client'];
        ModuleSetting::createRoleSettingEntry('TrAccessCard', $roles, $company);
        // TrAccessCard::addModuleSetting($company);
    }
}
