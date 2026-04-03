<?php

namespace App\Listeners;

use App\Events\DealEvent;
use App\Models\Deal;
use App\Models\User;
use App\Notifications\DealStageUpdated;
use App\Notifications\LeadAgentAssigned;
use Illuminate\Support\Facades\Notification;

class DealListener
{

    /**
     * Handle the event.
     *
     * @param DealEvent $event
     * @return void
     */

    public function handle(DealEvent $event)
    {
        $enquiry = Deal::with('leadAgent', 'leadAgent.user', 'contact')->findOrFail($event->deal->id);

        $companyId = $enquiry->company_id;

        $adminUsers = User::allAdmins($companyId);
        $usersToNotify = collect($adminUsers);

        if ($enquiry->deal_watcher) {
            $dealWatcher = User::find($enquiry->deal_watcher);
            if ($dealWatcher) {
                $usersToNotify->push($dealWatcher);
            }
        }

        if ($enquiry->contact->lead_owner) {
            $leadOwner = User::find($enquiry->contact->lead_owner);
            if ($leadOwner) {
                $usersToNotify->push($leadOwner);
            }
        }

        if ($enquiry->leadAgent && $enquiry->leadAgent->user) {
            $leadAgent = User::find($enquiry->leadAgent->user->id);
            if ($leadAgent) {
                $usersToNotify->push($leadAgent);
            }
        }

        if (user()) {
            $createdBy = User::find(user()->id);
            if ($createdBy) {
                $usersToNotify->push($createdBy);
            }
        }

        // Remove duplicate users by id
        $usersToNotify = $usersToNotify->unique('id');

        if ($event->notificationName == 'LeadAgentAssigned') {
            if ($enquiry->leadAgent && $enquiry->leadAgent->user) {
                Notification::send($usersToNotify, new LeadAgentAssigned($enquiry));
            }
        }

        if ($event->notificationName == 'StageUpdated') {
            if ($enquiry->leadAgent && $enquiry->leadAgent->user) {
                Notification::send($usersToNotify, new DealStageUpdated($enquiry));
            }
        }
    }

}
