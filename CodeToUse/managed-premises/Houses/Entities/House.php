<?php

namespace Modules\Houses\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use App\Models\ModuleSetting;
use Modules\Houses\Entities\Area;
use Modules\Houses\Entities\Tower;
use Modules\Houses\Entities\TypeHouse;

class House extends BaseModel
{
    use HasCompany;

    protected $table = 'houses';
    protected $guarded = ['id'];
    const MODULE_NAME = 'houses';

    public static function addModuleSetting($company)
    {
        // create admin, employee and client module settings
        $roles = ['admin', 'employee'];

        ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);

    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function tower()
    {
        return $this->belongsTo(Tower::class, 'tower_id');
    }

    public function typehouse()
    {
        return $this->belongsTo(TypeHouse::class, 'typehouse_id');
    }

    // public function guestbooks()
    // {
    //     return $this->hasMany(Guestbook::class);
    // }
}
