<?php

namespace App\Listeners;

use App\Events\AutoFollowUpReminderEvent;
use App\Models\User;
use App\Notifications\AutoFollowUpReminder;
use Illuminate\Support\Facades\Notification;

class AutoFollowUpReminderListener
{

    /**
     * Handle the event.
     *
     * @param AutoFollowUpReminderEvent $event
     * @return void
     */

    public function handle(AutoFollowUpReminderEvent $event)
    {

        $companyId = $event->followup->enquiry->company_id;

        $adminUsers = User::allAdmins($companyId);
        $usersToNotify = collect($adminUsers);

        // Add enquiry agent if assigned
        if (!is_null($event->followup->enquiry->leadAgent)) {
            $usersToNotify->push($event->followup->enquiry->leadAgent->user);
        }

        // Add the user who created the follow-up
        $followUpCreator = User::find($event->followup->added_by);
        if ($followUpCreator) {
            $usersToNotify->push($followUpCreator);
        }

        // Add the user who is watching the deal
        $dealWatcher = User::find($event->followup->enquiry->deal_watcher);
        if ($dealWatcher) {
            $usersToNotify->push($dealWatcher);
        }

        // Remove duplicates (in case any users are duplicated)
        $usersToNotify = $usersToNotify->unique('id');

        if ($usersToNotify->isNotEmpty()) {
            Notification::send($usersToNotify, new AutoFollowUpReminder($event->followup,$event->subject));
        }

    }

}
