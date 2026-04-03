<?php

namespace Modules\Units\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use App\Models\ModuleSetting;
use Modules\Units\Entities\Floor;
use Modules\Units\Entities\Tower;
use Modules\Units\Entities\TypeUnit;

class Unit extends BaseModel
{
    use HasCompany;

    protected $table = 'units';
    protected $guarded = ['id'];
    const MODULE_NAME = 'units';

    public static function addModuleSetting($company)
    {
        // create admin, employee and client module settings
        $roles = ['admin', 'employee', 'client'];

        ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);

    }

    public function floor()
    {
        return $this->belongsTo(Floor::class, 'floor_id');
    }

    public function tower()
    {
        return $this->belongsTo(Tower::class, 'tower_id');
    }

    public function typeunit()
    {
        return $this->belongsTo(TypeUnit::class, 'typeunit_id');
    }
}
