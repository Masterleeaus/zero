<?php

namespace Modules\Documents\Entities;
use App\Models\BaseModel;
use App\Models\ModuleSetting;

class LetterSetting extends BaseModel
{

    protected $guarded = ['id'];

    const MODULE_NAME = 'Documents';

    public static function addModuleSetting($company)
    {
        $roles = ['employee', 'admin'];
        ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);
    }

}

