<?php

namespace App\Events;

use App\Models\Quote;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class EstimateAcceptedEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $quote;

    public function __construct(Quote $quote)
    {
        $this->quote = $quote;
    }

}
