<?php

namespace Modules\TrNotes\Observers;

use Modules\TrNotes\Entities\Notes;

class NotesObserver
{

    public function saving(Notes $unit)
    {
        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if (company()) {
            $unit->company_id = company()->id;
        }
    }

}

