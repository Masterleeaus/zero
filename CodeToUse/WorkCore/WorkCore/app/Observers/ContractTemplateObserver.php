<?php

namespace App\Observers;

use App\Models\ContractTemplate;

class ContractTemplateObserver
{

    public function creating(ContractTemplate $service agreement)
    {

        if (user()) {
            $service agreement->added_by = user()->id;
        }

        if (company()) {
            $service agreement->company_id = company()->id;
        }

        $service agreement->contract_template_number = (int)ContractTemplate::max('contract_template_number') + 1;
    }

}
