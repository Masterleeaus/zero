<?php

namespace Modules\TrPackage\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;

class Ekspedisi extends BaseModel
{
    use HasCompany;

    protected $table = 'tr_package_ekspedisi';
    protected $guarded = ['id'];
}

