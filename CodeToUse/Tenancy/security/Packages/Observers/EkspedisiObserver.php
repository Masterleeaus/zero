<?php

namespace Modules\TrPackage\Observers;

use Modules\TrPackage\Entities\Ekspedisi;

class EkspedisiObserver
{

    public function saving(Ekspedisi $unit)
    {
        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if (company()) {
            $unit->company_id = company()->id;
        }
    }

}

