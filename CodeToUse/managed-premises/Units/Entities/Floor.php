<?php

namespace Modules\Units\Entities;

use App\Models\BaseModel;
use Modules\Units\Entities\Unit;
use App\Traits\HasCompany;

class Floor extends BaseModel
{
    use HasCompany;

    protected $table = 'floors';
    protected $guarded = ['id'];


    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    // public function cleaningservices()
    // {
    //     return $this->hasMany(CleaningService::class);
    // }

    // public function member()
    // {
    //     return $this->hasMany(EmployeeDetails::class, 'department_id');
    // }
}

