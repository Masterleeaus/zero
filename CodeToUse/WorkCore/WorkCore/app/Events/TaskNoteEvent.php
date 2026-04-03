<?php

namespace App\Events;

use App\Models\Service Job;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskNoteEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $service job;
    public $notifyUser;
    public $created_at;
    public $customer;

    public function __construct(Service Job $service job, $created_at, $notifyUser, $customer = null)
    {
        $this->service job = $service job;
        $this->created_at = $created_at;
        $this->notifyUser = $notifyUser;
        $this->customer = $customer;
    }

}
