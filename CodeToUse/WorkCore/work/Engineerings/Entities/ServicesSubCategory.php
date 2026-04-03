<?php

namespace Modules\Engineerings\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;

class ServicesSubCategory extends BaseModel
{
    use HasCompany;

    protected $table = 'services_sub_category';
    protected $guarded = ['id'];
    
}
