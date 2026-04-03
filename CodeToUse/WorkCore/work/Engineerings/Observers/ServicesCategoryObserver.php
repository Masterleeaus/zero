<?php

namespace Modules\Engineerings\Observers;
use Modules\Engineerings\Entities\ServicesCategory;

class ServicesCategoryObserver
{
    public function saving(ServicesCategory $unit)
    {
        if (company()) {
            $unit->company_id = company()->id;
        }
    }
}

