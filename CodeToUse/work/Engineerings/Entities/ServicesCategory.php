<?php

namespace Modules\Engineerings\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use Modules\Engineerings\Entities\ServicesSubCategory;

class ServicesCategory extends BaseModel
{
    use HasCompany;

    protected $table = 'services_category';
    protected $guarded = ['id'];

    public function subCategories()
    {
        return $this->hasMany(ServicesSubCategory::class, 'category_id');
    }
}
