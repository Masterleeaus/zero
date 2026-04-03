<?php

namespace Modules\TrPackage\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use App\Models\ModuleSetting;
use Modules\Units\Entities\Unit;
use Modules\TrPackage\Entities\Ekspedisi;
use Modules\TrPackage\Entities\TypePackage;
use Modules\TrPackage\Entities\PackageItems;

class Package extends BaseModel
{
    use HasCompany;

    protected $table = 'tr_package';
    protected $guarded = ['id'];
    const MODULE_NAME = 'trpackage';

    public static function addModuleSetting($company)
    {
        $roles = ['admin', 'employee'];
        ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function ekspedisi()
    {
        return $this->belongsTo(Ekspedisi::class, 'ekspedisi_id');
    }

    public function type()
    {
        return $this->belongsTo(TypePackage::class, 'type_id');
    }

    public function items()
    {
        return $this->hasMany(PackageItems::class, 'package_id');
    }
}
