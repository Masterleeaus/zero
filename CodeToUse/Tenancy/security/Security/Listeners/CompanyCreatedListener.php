<?php

namespace Modules\Security\Listeners;
use Modules\Security\Entities\Security;

class CompanyCreatedListener
{

    public function handle($event)
    {
        $company = $event->company;
        Security::addModuleSetting($company);
    }

}
