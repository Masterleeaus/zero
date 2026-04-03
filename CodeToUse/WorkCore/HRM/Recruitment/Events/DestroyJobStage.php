<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class DestroyJobStage
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $jobStage;

    public function __construct($jobStage)
    {
        $this->jobStage = $jobStage;
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
