<?php

namespace Modules\FormBuilder\Events;

use Illuminate\Queue\SerializesModels;

class CreateForm
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $form_builder;

    public function __construct($request,$form_builder)
    {
        $this->request = $request;
        $this->form_builder = $form_builder;
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
