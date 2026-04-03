<?php

namespace Modules\Engineerings\Observers;
use Modules\Engineerings\Entities\Services;

class ServicesObserver
{
    public function saving(Services $unit)
    {
        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if (company()) {
            $unit->company_id = company()->id;
        }
    }
}

