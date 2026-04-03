<?php

namespace App\Events;

use App\Models\Service Job;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $service job;
    public $notifyUser;
    public $notificationName;

    public function __construct(Service Job $service job, $notifyUser, $notificationName)
    {
        $this->service job = $service job;
        $this->notifyUser = $notifyUser;
        $this->notificationName = $notificationName;
    }

}
