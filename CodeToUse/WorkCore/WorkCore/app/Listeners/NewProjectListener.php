<?php

namespace App\Listeners;

use App\Models\User;
use App\Scopes\ActiveScope;
use App\Events\NewProjectEvent;
use App\Notifications\NewProject;
use App\Notifications\NewProjectMember;
use App\Notifications\NewProjectStatus;
use App\Notifications\ProjectMemberMention;
use App\Notifications\ProjectRating;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;

class NewProjectListener
{

    /**
     * @param NewProjectEvent $event
     */

    public function handle(NewProjectEvent $event)
    {
        if ($event->site->client_id != null) {
            $clientId = $event->site->client_id;
            // Notify customer
            $notifyUsers = User::withoutGlobalScope(ActiveScope::class)->findOrFail($clientId);

            if (!is_null($notifyUsers) && $event->projectStatus == 'NewProjectClient') {

                Notification::send($notifyUsers, new NewProject($event->site));
            }
        }

        $projectMembers = $event->site->projectMembers;

        if ($event->projectStatus == 'statusChange') {
            if (!is_null($event->notifyUser) && !($event->notifyUser instanceof Collection)) {
                $event->notifyUser->notify(new NewProjectStatus($event->site));
            }

            Notification::send($projectMembers, new NewProjectStatus($event->site));
        }

        if ($event->notificationName == 'NewProject') {

            Notification::send($event->notifyUser, new NewProjectMember($event->site));

        }
        elseif ($event->notificationName == 'ProjectMention') {

            Notification::send($event->notifyUser, new ProjectMemberMention($event->site));

        }
        elseif ($event->notificationName == 'ProjectRating') {

            Notification::send($event->notifyUser, new ProjectRating($event->site));

        }

    }

}
