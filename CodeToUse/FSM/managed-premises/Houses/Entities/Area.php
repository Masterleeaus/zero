<?php

namespace Modules\Houses\Entities;

use App\Models\BaseModel;
use Modules\Houses\Entities\House;
use App\Traits\HasCompany;

class Area extends BaseModel
{
    use HasCompany;

    protected $table = 'areas';
    protected $guarded = ['id'];


    public function houses()
    {
        return $this->hasMany(House::class);
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

