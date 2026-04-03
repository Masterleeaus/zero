<?php

namespace Modules\Kontrak\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use App\Models\ModuleSetting;

class Kontrak extends BaseModel
{
    use HasCompany;
    const MODULE_NAME = 'kontrak';

    public static function addModuleSetting($company)
    {
        // create admin, employee and client module settings
        $roles = ['admin', 'employee'];
        ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);
    }
}
