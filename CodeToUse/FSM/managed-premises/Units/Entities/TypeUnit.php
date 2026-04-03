<?php

namespace Modules\Units\Entities;

use App\Models\BaseModel;
use Modules\Units\Entities\Unit;
use App\Traits\HasCompany;


class TypeUnit extends BaseModel
{
    use HasCompany;

    protected $table = 'type_units';
    protected $guarded = ['id'];

    public function units()
    {
        return $this->hasMany(Unit::class);
    }
}
