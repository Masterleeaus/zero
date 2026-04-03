<?php

namespace App\Events;

use App\Models\Enquiry;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadImportEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notificationName;
    /**
     * @var Enquiry
     */
    public $leadContact;

    public function __construct(Enquiry $leadContact, $notificationName)
    {
        $this->leadContact = $leadContact;
        $this->notificationName = $notificationName;
    }

}
