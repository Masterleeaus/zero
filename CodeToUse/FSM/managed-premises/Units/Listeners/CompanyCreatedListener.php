<?php

namespace Modules\Units\Listeners;

use Modules\Units\Entities\Unit;

class CompanyCreatedListener
{

    public function handle($event)
    {
        $company = $event->company;
        Unit::addModuleSetting($company);
    }

}
