<?php

namespace Modules\FieldItems\Observers;

use Modules\FieldItems\Entities\ItemSubCategory;

class ItemSubCategoryObserver
{

    public function creating(ItemSubCategory $itemSubCategory)
    {
        if (company()) {
            $itemSubCategory->company_id = company()->id;
        }
    }

}
