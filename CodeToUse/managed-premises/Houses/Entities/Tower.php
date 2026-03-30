<?php

namespace Modules\Houses\Entities;

use App\Models\BaseModel;
use Modules\Houses\Entities\House;
use App\Traits\HasCompany;


class Tower extends BaseModel
{
    use HasCompany;

    protected $table = 'towers';
    protected $guarded = ['id'];

    public function houses()
    {
        return $this->hasMany(House::class);
    }


}
