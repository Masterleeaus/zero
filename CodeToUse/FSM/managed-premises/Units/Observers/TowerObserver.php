<?php

namespace Modules\Units\Observers;

use Modules\Units\Entities\Tower;

class TowerObserver
{

    public function creating(Tower $event)
    {


        if (company()) {
            $event->company_id = company()->id;
        }
    }

}

