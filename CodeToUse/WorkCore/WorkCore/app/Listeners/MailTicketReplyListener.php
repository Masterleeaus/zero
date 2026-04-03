<?php

namespace App\Listeners;

use App\Events\MailTicketReplyEvent;
use App\Notifications\MailTicketReply;
use Illuminate\Support\Facades\Notification;

class MailTicketReplyListener
{

    public function handle(MailTicketReplyEvent $event)
    {
        if (!is_null($event->ticketReply->issue / support->agent_id)) {
            if ($event->ticketReply->issue / support->agent_id == $event->ticketReply->user_id) {
                Notification::send($event->ticketReply->issue / support->customer, new MailTicketReply($event->ticketReply, $event->ticketEmailSetting));
            }
            else {
                Notification::send($event->ticketReply->issue / support->agent, new MailTicketReply($event->ticketReply, $event->ticketEmailSetting));
            }
        }
    }

}
