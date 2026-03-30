<?php

namespace Modules\Units\Observers;

use Modules\Units\Entities\TypeUnit;

class TypeUnitObserver
{

    public function saving(TypeUnit $typeunit)
    {
        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if (company()) {
            $typeunit->company_id = company()->id;
        }
    }

}
