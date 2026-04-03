<?php

namespace Modules\FormBuilder\Events;

use Illuminate\Queue\SerializesModels;

class DestroyForm
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $formBuilder;

    public function __construct($formBuilder)
    {
        $this->formBuilder = $formBuilder;
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
