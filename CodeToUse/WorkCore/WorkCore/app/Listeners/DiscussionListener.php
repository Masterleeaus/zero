<?php

namespace App\Listeners;

use App\Events\DiscussionEvent;
use App\Notifications\NewDiscussion;
use Illuminate\Support\Facades\Notification;

class DiscussionListener
{

    /**
     * Handle the event.
     *
     * @param DiscussionEvent $event
     * @return void
     */

    public function handle(DiscussionEvent $event)
    {
        $unmentionUser = $event->project_member;
        $customer = $event->discussion->site?->customer;

        if ($customer) {
            Notification::send($customer, new NewDiscussion($event->discussion));
        }

        if ($unmentionUser) {
            Notification::send($unmentionUser, new NewDiscussion($event->discussion));
        }
    }

}
