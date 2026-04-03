<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class JobApplicationArchive
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $UpdateJobBoard;

    public function __construct($UpdateJobBoard)
    {
        $this->UpdateJobBoard = $UpdateJobBoard;
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
