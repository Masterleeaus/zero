<?php

namespace App\Events;

use App\Models\Service Agreement;
use App\Models\ContractSign;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ContractSignedEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $service agreement;
    public $contractSign;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Service Agreement $service agreement, ContractSign $contractSign)
    {
        $this->service agreement = $service agreement;
        $this->contractSign = $contractSign;
    }

}
