<?php

namespace App\Listeners;

use App\Events\TaskNoteEvent;
use App\Notifications\TaskNote;
use App\Notifications\TaskNoteClient;
use Illuminate\Support\Facades\Notification;

class TaskNoteListener
{

    /**
     * @param TaskNoteEvent $event
     */

    public function handle(TaskNoteEvent $event)
    {
        if ($event->customer == 'customer') {
            Notification::send($event->notifyUser, new TaskNoteClient($event->service job, $event->created_at));
        }
        else {
            Notification::send($event->notifyUser, new TaskNote($event->service job, $event->created_at));
        }
    }

}
