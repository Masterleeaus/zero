<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class UpdateJobBoard
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $request;
    public $jobBoard;

    public function __construct($request, $jobBoard)
    {
        $this->request = $request;
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
