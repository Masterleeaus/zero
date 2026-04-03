<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class DestroyJobCategory
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $jobCategory;

    public function __construct($jobCategory)
    {
        $this->jobCategory = $jobCategory;
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
