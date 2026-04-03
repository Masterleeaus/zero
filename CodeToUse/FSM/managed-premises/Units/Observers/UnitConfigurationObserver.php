<?php

namespace Modules\Units\Observers;

use Modules\Units\Entities\UsersUnit;

class UnitConfigurationObserver
{
    public function saving(UsersUnit $unit)
    {
        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if (company()) {
            $unit->company_id = company()->id;
        }
    }
}

