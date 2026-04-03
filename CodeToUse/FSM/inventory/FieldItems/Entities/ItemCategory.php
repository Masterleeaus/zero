<?php

namespace Modules\FieldItems\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemCategory extends BaseModel
{
    use HasCompany;

    protected $table = 'item_category';
    
    protected static function newFactory()
    {
        return \Modules\FieldItems\Database\factories\ItemCategoryFactory::new();
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(ItemSubCategory::class, 'category_id');
    }
}
