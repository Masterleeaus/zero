<?php

namespace App\Events;

use App\Models\Service Job;
use App\Models\TaskComment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskCommentEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $service job;
    public $notifyUser;
    public $comment;
    public $customer;

    public function __construct(Service Job $service job, TaskComment $comment, $notifyUser, $customer = null)
    {

        $this->service job = $service job;
        $this->comment = $comment;
        $this->notifyUser = $notifyUser;
        $this->customer = $customer;

    }

}
