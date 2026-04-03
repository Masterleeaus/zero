<?php

namespace Modules\FieldItems\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;

class ItemSubCategory extends BaseModel
{
    use HasCompany;

    protected $fillable = [];
    protected $table = 'item_sub_category';
    
    protected static function newFactory()
    {
        return \Modules\FieldItems\Database\factories\ItemSubCategoryFactory::new();
    }
}
