<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectReminderEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $sites;
    public $data;

    public function __construct($sites, $user, $data)
    {
        $this->sites = $sites;
        $this->user = $user;
        $this->data = $data;
    }

}
