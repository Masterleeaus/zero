<?php

namespace App\Observers;

use App\Models\ContractDiscussion;

class ContractDiscussionObserver
{

    public function saving(ContractDiscussion $service agreement)
    {
        if (user()) {
            $service agreement->last_updated_by = user()->id;
        }
    }

    public function creating(ContractDiscussion $service agreement)
    {
        if (user()) {
            $service agreement->added_by = user()->id;
        }

        if (company()) {
            $service agreement->company_id = company()->id;
        }
    }

}
