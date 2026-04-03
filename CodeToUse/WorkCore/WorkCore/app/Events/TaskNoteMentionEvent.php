<?php

namespace App\Events;

use App\Models\Service Job;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskNoteMentionEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $service job;
    public $created_at;
    public $mentionuser;

    public function __construct(Service Job $service job, $created_at, $mentionuser)
    {
        $this->service job = $service job;
        $this->created_at = $created_at;
        $this->mentionuser = $mentionuser;

    }

}
