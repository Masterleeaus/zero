<?php

namespace Modules\Trnotes\Listeners;

use Modules\TrNotes\Entities\Notes;

class CompanyCreatedListener
{

    public function handle($event)
    {
        $company = $event->company;
        Notes::addModuleSetting($company);
    }

}
