<?php

namespace Modules\Security\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use App\Models\ModuleSetting;

class Security extends BaseModel
{
    use HasCompany;
    const MODULE_NAME = 'security';

    public static function addModuleSetting($company)
    {
        // create admin, employee and client module settings
        $roles = ['admin', 'employee'];
        ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);
    }
}
