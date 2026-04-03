<?php

namespace Modules\TrWorkPermits\Observers;

use Modules\TrWorkPermits\Entities\WorkPermits;

class WorkPermitsObserver
{

    public function saving(WorkPermits $unit)
    {
        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if (company()) {
            $unit->company_id = company()->id;
        }
    }

}

