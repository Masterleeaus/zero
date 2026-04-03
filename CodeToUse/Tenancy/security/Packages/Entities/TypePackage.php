<?php

namespace Modules\TrPackage\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;

class TypePackage extends BaseModel
{
    use HasCompany;

    protected $table = 'tr_package_type';
    protected $guarded = ['id'];
}

