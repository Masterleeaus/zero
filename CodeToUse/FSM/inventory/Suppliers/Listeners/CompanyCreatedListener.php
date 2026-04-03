<?php

namespace Modules\Suppliers\Listeners;
use Modules\Suppliers\Entities\Supplier;

class CompanyCreatedListener
{

    public function handle($event)
    {
        $company = $event->company;
        Supplier::addModuleSetting($company);
    }

}
