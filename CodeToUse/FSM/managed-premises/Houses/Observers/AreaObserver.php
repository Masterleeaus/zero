<?php

namespace Modules\Houses\Observers;

use Modules\Houses\Entities\Area;

class AreaObserver
{

    public function creating(Area $event)
    {


        if (company()) {
            $event->company_id = company()->id;
        }
    }

}

