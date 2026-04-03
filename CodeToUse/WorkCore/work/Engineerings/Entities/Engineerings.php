<?php

namespace Modules\Engineerings\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use App\Models\ModuleSetting;

class Engineerings extends BaseModel
{
    use HasCompany;
    const MODULE_NAME = 'engineerings';

    public static function addModuleSetting($company)
    {
        $roles = ['admin', 'employee'];
        ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);
    }
}
