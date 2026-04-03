<?php

namespace Modules\TrInOutPermit\Entities;

use App\Models\User;
use App\Models\BaseModel;
use App\Traits\HasCompany;
use App\Models\ModuleSetting;
use Modules\Units\Entities\Unit;
use Modules\Units\Entities\Floor;
use Modules\Units\Entities\Tower;
use Modules\Units\Entities\TypeUnit;
use Modules\Accountings\Entities\Pnl;

class TrInOutPermit extends BaseModel
{
    use HasCompany;

    protected $table = 'tr_in_out_permit';
    protected $guarded = ['id'];
    const MODULE_NAME = 'trinoutpermit';
    // protected $dates = ['date'];

    public static function addModuleSetting($company)
    {
        $roles = ['admin', 'employee', 'client'];
        ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function tower()
    {
        return $this->unit->belongsTo(Tower::class, 'tower_id');
    }

    public function pnl()
    {
        return $this->belongsTo(Pnl::class, 'pnl_id');
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class, 'floor_id');
    }

    public function typeunit()
    {
        return $this->belongsTo(TypeUnit::class, 'typeunit_id');
    }

    public function approved()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvedBm()
    {
        return $this->belongsTo(User::class, 'approved_bm');
    }

    public function validated()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
