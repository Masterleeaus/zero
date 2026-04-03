<?php

namespace Modules\FormBuilder\Events;

use Illuminate\Queue\SerializesModels;

class UpdateFormField
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $form;

    public function __construct($request,$form)
    {
        $this->request = $request;
        $this->form = $form;
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
