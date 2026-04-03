<?php

namespace App\Listeners;

use App\Events\EstimateRequestAcceptedEvent;
use Illuminate\Support\Facades\Notification;
use App\Notifications\EstimateRequestAccepted;

class EstimateRequestAcceptedListener
{

    /**
     * Handle the event.
     */
    public function handle(EstimateRequestAcceptedEvent $event): void
    {
        $notifiable = $event->estimateRequest->customer;

        if (isset($notifiable->email)){
            Notification::send($notifiable, new EstimateRequestAccepted($event->estimateRequest));
        }
    }

}
