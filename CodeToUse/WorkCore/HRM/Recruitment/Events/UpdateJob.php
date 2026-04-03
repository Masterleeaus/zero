<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class UpdateJob
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $request;
    public $job;

    public function __construct($job, $request)
    {
        $this->request = $request;
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
