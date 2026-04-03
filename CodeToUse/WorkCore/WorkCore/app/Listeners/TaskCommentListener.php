<?php

namespace App\Listeners;

use App\Events\TaskCommentEvent;
use App\Notifications\TaskComment;
use App\Notifications\TaskCommentClient;
use Illuminate\Support\Facades\Notification;

class TaskCommentListener
{

    /**
     * Handle the event.
     *
     * @param TaskCommentEvent $event
     * @return void
     */

    public function handle(TaskCommentEvent $event)
    {
        if ($event->customer == 'customer') {
            Notification::send($event->notifyUser, new TaskCommentClient($event->service job, $event->comment));
        }
        else {
            Notification::send($event->notifyUser, new TaskComment($event->service job, $event->comment));
        }
    }

}
