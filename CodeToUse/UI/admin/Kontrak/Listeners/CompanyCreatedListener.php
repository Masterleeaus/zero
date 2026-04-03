<?php

namespace Modules\Kontrak\Listeners;
use Modules\Kontrak\Entities\Kontrak;

class CompanyCreatedListener
{

    public function handle($event)
    {
        $company = $event->company;
        Kontrak::addModuleSetting($company);
    }

}
