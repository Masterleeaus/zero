<?php

namespace Modules\FieldItems\Observers;

use Modules\FieldItems\Entities\ItemCategory;

class ItemCategoryObserver
{

    public function creating(ItemCategory $itemCategory)
    {
        if (company()) {
            $itemCategory->company_id = company()->id;
        }
    }

}
