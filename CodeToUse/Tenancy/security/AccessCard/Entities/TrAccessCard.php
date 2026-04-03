<?php

namespace Modules\TrAccessCard\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use App\Models\ModuleSetting;
use Modules\Units\Entities\Unit;
use Modules\Tenancy\Entities\CardItems;

class TrAccessCard extends BaseModel
{
    use HasCompany;

    protected $table = 'tr_access_card';
    protected $guarded = ['id'];
    const MODULE_NAME = 'traccesscard';

    public static function addModuleSetting($company)
    {
        $roles = ['admin', 'employee', 'client'];
        ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function items()
    {
        return $this->hasMany(CardItems::class, 'card_id');
    }
}
