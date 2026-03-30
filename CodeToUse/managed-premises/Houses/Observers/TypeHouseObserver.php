<?php

namespace Modules\Houses\Observers;

use Modules\Houses\Entities\TypeHouse;

class TypeHouseObserver
{

    public function saving(TypeHouse $typehouse)
    {
        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if (company()) {
            $typehouse->company_id = company()->id;
        }
    }

}
