<?php

namespace Modules\Units\Entities;

use App\Models\BaseModel;
use Modules\Units\Entities\Unit;
use App\Traits\HasCompany;


class Tower extends BaseModel
{
    use HasCompany;

    protected $table = 'towers';
    protected $guarded = ['id'];

    public function units()
    {
        return $this->hasMany(Unit::class);
    }


}
