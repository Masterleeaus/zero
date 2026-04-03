<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class DestroyInterviewSchedule
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $interviewSchedule;

    public function __construct($interviewSchedule)
    {
        $this->interviewSchedule = $interviewSchedule;
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
