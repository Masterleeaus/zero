<?php

namespace Modules\Security\Observers;

use Modules\Security\Entities\Security;

class SecurityObserver
{

    public function saving(Security $unit)
    {
        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if (company()) {
            $unit->company_id = company()->id;
        }
    }

}

