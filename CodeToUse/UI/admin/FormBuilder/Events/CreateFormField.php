<?php

namespace Modules\FormBuilder\Events;

use Illuminate\Queue\SerializesModels;

class CreateFormField
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $formbuilder;

    public function __construct($request,$formbuilder)
    {
        $this->request = $request;
        $this->formbuilder = $formbuilder;
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
