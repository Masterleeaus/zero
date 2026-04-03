<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class UpdateCustomQuestion
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $request;
    public $customQuestion;

    public function __construct($request, $customQuestion)
    {
        $this->request = $request;
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
