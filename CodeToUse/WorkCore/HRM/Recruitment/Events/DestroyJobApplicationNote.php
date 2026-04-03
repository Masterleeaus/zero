<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class DestroyJobApplicationNote
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $note;

    public function __construct($note)
    {
        $this->note = $note;
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
