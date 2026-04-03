<?php

namespace Modules\Units\Observers;

use Modules\Units\Entities\Floor;

class FloorObserver
{

    public function creating(Floor $event)
    {


        if (company()) {
            $event->company_id = company()->id;
        }
    }

}

