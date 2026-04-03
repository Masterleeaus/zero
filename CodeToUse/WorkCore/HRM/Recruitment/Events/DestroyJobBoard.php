<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class DestroyJobBoard
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $jobBoard;

    public function __construct($jobBoard)
    {
        $this->jobBoard = $jobBoard;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
