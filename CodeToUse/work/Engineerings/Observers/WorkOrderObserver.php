<?php

namespace Modules\Engineerings\Observers;
use Modules\Engineerings\Entities\WorkOrder;

class WorkOrderObserver
{
    public function saving(WorkOrder $unit)
    {
        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if (company()) {
            $unit->company_id = company()->id;
        }
    }
}

