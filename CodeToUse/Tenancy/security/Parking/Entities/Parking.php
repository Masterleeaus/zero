<?php

namespace Modules\Parking\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use App\Models\ModuleSetting;
use Modules\Units\Entities\Unit;
use Modules\Parking\Entities\ParkingItems;
use App\Traits\CustomFieldsTrait;

class Parking extends BaseModel
{
    use CustomFieldsTrait;
    use HasCompany;

    protected $table = 'tenan_parkir';
    protected $guarded = ['id'];
    const MODULE_NAME = 'parking';

    public static function addModuleSetting($company)
    {
        $roles = ['admin', 'employee'];
        ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);
    }

    public function items()
    {
        return $this->hasMany(ParkingItems::class, 'parkir_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

}
