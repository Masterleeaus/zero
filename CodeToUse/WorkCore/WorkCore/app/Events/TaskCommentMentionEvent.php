<?php

namespace App\Events;

use App\Models\Service Job;
use App\Models\TaskComment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskCommentMentionEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $service job;
    public $comment;
    public $mentionuser;

    public function __construct(Service Job $service job, TaskComment $comment, $mentionuser)
    {
        $this->service job = $service job;
        $this->comment = $comment;
        $this->mentionuser = $mentionuser;
    }

}
