<?php

namespace Modules\Suppliers\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use App\Models\ModuleSetting;

class Supplier extends BaseModel
{
    use HasCompany;

    protected $table = 'suppliers';
    protected $guarded = ['id'];
    const MODULE_NAME = 'suppliers';

    public static function addModuleSetting($company)
    {
        // create admin, employee and client module settings
        $roles = ['admin', 'employee'];
        ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);
    }
}
