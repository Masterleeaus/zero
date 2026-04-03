<?php

namespace App\Events;

use App\Models\Service Job;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AutoTaskReminderEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $service job;

    public function __construct(Service Job $service job)
    {
        $this->service job = $service job;
    }

}
