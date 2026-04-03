<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class DestroyJob
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $job;

    public function __construct($job)
    {
        $this->job = $job;
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
