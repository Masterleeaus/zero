<?php

namespace App\Listeners;

use App\Events\DiscussionReplyEvent;
use App\Notifications\NewDiscussionReply;
use Illuminate\Support\Facades\Notification;

class DiscussionReplyListener
{

    /**
     * Handle the event.
     *
     * @param DiscussionReplyEvent $event
     * @return void
     */

    public function handle(DiscussionReplyEvent $event)
    {
        $customer = $event->discussionReply?->discussion?->site?->customer;

        if ($customer) {
            Notification::send($customer, new NewDiscussionReply($event->discussionReply));
        }

        Notification::send($event->notifyUser, new NewDiscussionReply($event->discussionReply));
    }

}
