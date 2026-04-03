<?php

namespace Modules\TrNotes\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use App\Models\ModuleSetting;

class Notes extends BaseModel
{
    use HasCompany;

    const MODULE_NAME = 'trnotes';
    protected $table = 'notes';
    protected $guarded = ['id'];

    public static function addModuleSetting($company)
    {
        // create admin, employee and client module settings
        $roles = ['admin', 'employee'];
        ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);
    }

}

