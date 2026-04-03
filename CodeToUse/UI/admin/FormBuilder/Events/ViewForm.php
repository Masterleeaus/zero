<?php

namespace Modules\FormBuilder\Events;

use Illuminate\Queue\SerializesModels;

class ViewForm
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $lead;
    public $form;

    public function __construct($request,$lead,$form)
    {
        $this->request = $request;
        $this->lead = $lead;
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
