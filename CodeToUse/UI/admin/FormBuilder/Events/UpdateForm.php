<?php

namespace Modules\FormBuilder\Events;

use Illuminate\Queue\SerializesModels;

class UpdateForm
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $formBuilder;

    public function __construct($request,$formBuilder)
    {
        $this->request = $request;
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
