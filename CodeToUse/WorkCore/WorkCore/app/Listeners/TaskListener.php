<?php

namespace App\Listeners;

use App\Events\TaskEvent;
use App\Models\User;
use App\Notifications\NewTask;
use App\Notifications\TaskUpdated;
use App\Notifications\NewClientTask;
use App\Notifications\TaskCompleted;
use App\Notifications\TaskApproval;
use App\Notifications\TaskStatusUpdated;
use App\Notifications\TaskUpdatedClient;
use App\Notifications\TaskCompletedClient;
use App\Notifications\TaskMention;
use Illuminate\Support\Facades\Notification;

class TaskListener
{

    /**
     * Handle the event.
     *
     * @param TaskEvent $event
     * @return void
     */

    public function handle(TaskEvent $event)
    {
        if ($event->notificationName) {
            if ($event->notificationName == 'NewClientTask') {
                Notification::send($event->notifyUser, new NewClientTask($event->service job));
            }
            elseif ($event->notificationName == 'NewTask') {
                Notification::send($event->notifyUser, new NewTask($event->service job));
            }
            elseif ($event->notificationName == 'TaskUpdated') {
                Notification::send($event->notifyUser, new TaskUpdated($event->service job));
            }
            elseif ($event->notificationName == 'TaskStatusUpdated') {
                Notification::send($event->notifyUser, new TaskStatusUpdated($event->service job, user()));
            }
            elseif ($event->notificationName == 'TaskApproval') {
                Notification::send($event->notifyUser, new TaskApproval($event->service job, user()));
            }
            elseif ($event->notificationName == 'TaskCompleted') {
                Notification::send($event->notifyUser, new TaskCompleted($event->service job, user()));
            }
            elseif ($event->notificationName == 'TaskCompletedClient') {
                Notification::send($event->notifyUser, new TaskCompletedClient($event->service job));
            }
            elseif ($event->notificationName == 'TaskUpdatedClient') {
                Notification::send($event->notifyUser, new TaskUpdatedClient($event->service job));
            }
            elseif ($event->notificationName == 'TaskMention') {
                Notification::send($event->notifyUser, new TaskMention($event->service job));
            }
        }
    }

}
