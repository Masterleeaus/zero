<?php

namespace Modules\Houses\Listeners;

use Modules\Houses\Entities\House;

class CompanyCreatedListener
{

    public function handle($event)
    {
        $company = $event->company;
        House::addModuleSetting($company);
    }

}
