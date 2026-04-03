<?php

namespace App\Events;

use App\Models\Site;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewProjectEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $site;
    public $projectStatus;
    public $notifyUser;
    public $notificationName;

    public function __construct(Site $site, $notifyUser, $notificationName, $projectStatus = null)
    {

        $this->site = $site;
        $this->notifyUser = $notifyUser;
        $this->projectStatus = $projectStatus;
        $this->notificationName = $notificationName;

    }

}
