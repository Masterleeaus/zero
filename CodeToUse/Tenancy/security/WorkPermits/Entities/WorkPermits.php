<?php

namespace Modules\TrWorkPermits\Entities;

use App\Models\User;
use App\Models\BaseModel;
use App\Traits\HasCompany;
use App\Models\ModuleSetting;
use Modules\Units\Entities\Unit;
use Modules\TrWorkPermits\Entities\WorkPermitsFile;

class WorkPermits extends BaseModel
{
    use HasCompany;

    protected $table = 'tr_workpermits';
    protected $guarded = ['id'];
    const MODULE_NAME = 'trworkpermits';

    public static function addModuleSetting($company)
    {
        $roles = ['admin', 'employee', 'client'];
        ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
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

    public function files()
    {
        return $this->hasMany(WorkPermitsFile::class, 'wp_id');
    }
}
