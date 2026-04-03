<?php

namespace App\Listeners;

use App\Events\FileUploadEvent;
use App\Models\Site;
use App\Models\User;
use App\Notifications\FileUpload;
use App\Scopes\ActiveScope;
use Illuminate\Support\Facades\Notification;

class FileUploadListener
{

    /**
     * Handle the event.
     *
     * @param FileUploadEvent $event
     * @return void
     */

    public function handle(FileUploadEvent $event)
    {
        $site = Site::findOrFail($event->fileUpload->project_id);
        Notification::send($site->projectMembers, new FileUpload($event->fileUpload));

        if (($event->fileUpload->site->client_id != null)) {
            // Notify customer
            $notifyUser = User::withoutGlobalScope(ActiveScope::class)->findOrFail($event->fileUpload->site->client_id);

            if ($notifyUser) {
                Notification::send($notifyUser, new FileUpload($event->fileUpload));
            }
        }

    }

}
