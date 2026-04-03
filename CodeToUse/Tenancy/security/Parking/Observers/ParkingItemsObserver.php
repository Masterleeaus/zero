<?php

namespace Modules\Parking\Observers;

use Modules\Parking\Entities\ParkingItems;

class ParkingItemsObserver
{

    public function saving(ParkingItems $unit)
    {
        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if (company()) {
            $unit->company_id = company()->id;
        }
    }

}

