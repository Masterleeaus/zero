<?php

namespace Modules\Documents\Observers;
use Modules\Documents\Entities\Template;

class TemplateObserver
{

    public function creating(Template $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
