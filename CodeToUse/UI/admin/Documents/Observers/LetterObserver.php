<?php

namespace Modules\Documents\Observers;
use Modules\Documents\Entities\Documents;

class LetterObserver
{

    public function creating(Documents $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
