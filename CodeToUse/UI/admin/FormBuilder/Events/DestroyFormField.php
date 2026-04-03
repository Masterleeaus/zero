<?php

namespace Modules\FormBuilder\Events;

use Illuminate\Queue\SerializesModels;

class DestroyFormField
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $form;

    public function __construct($form)
    {
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
