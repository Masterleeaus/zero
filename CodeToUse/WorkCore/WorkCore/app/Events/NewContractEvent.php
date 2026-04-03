<?php

namespace App\Events;

use App\Models\Service Agreement;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewContractEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $service agreement;

    public function __construct(Service Agreement $service agreement)
    {
        $this->service agreement = $service agreement;
    }

}
