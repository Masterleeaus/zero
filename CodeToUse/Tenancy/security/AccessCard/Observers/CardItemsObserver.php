<?php

namespace Modules\TrAccessCard\Observers;

use Modules\TrAccessCard\Entities\CardItems;

class CardItemsObserver
{
    public function saving(CardItems $unit)
    {
        if (company()) {
            $unit->company_id = company()->id;
        }
    }
}

