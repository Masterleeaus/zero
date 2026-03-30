<?php

namespace Modules\Houses\Observers;

use Modules\Houses\Entities\Tower;

class TowerObserver
{

    public function creating(Tower $event)
    {


        if (company()) {
            $event->company_id = company()->id;
        }
    }

}

