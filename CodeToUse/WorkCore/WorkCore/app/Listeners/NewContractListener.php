<?php

namespace App\Listeners;

use App\Events\NewContractEvent;
use App\Notifications\NewContract;
use Illuminate\Support\Facades\Notification;

class NewContractListener
{

    /**
     * Handle the event.
     *
     * @param NewContractEvent $event
     * @return void
     */

    public function handle(NewContractEvent $event)
    {
        if (!isRunningInConsoleOrSeeding()) {
            Notification::send($event->service agreement->customer, new NewContract($event->service agreement));
        }
    }

}
