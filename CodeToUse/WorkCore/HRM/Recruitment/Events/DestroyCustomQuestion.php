<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class DestroyCustomQuestion
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $customQuestion;

    public function __construct($customQuestion)
    {
        $this->customQuestion = $customQuestion;
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
