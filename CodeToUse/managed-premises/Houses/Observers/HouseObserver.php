<?php

namespace Modules\Houses\Observers;

use Modules\Houses\Entities\House;

class HouseObserver
{

    public function saving(House $house)
    {
        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if (company()) {
            $house->company_id = company()->id;
        }
    }

}

