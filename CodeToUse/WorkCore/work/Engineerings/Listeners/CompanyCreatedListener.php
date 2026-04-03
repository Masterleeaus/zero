<?php

namespace Modules\Engineerings\Listeners;
use Modules\Engineerings\Entities\Engineerings;

class CompanyCreatedListener
{
    public function handle($event)
    {
        $company = $event->company;
        Engineerings::addModuleSetting($company);
    }
}
