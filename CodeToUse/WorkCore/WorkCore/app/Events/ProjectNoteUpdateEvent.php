<?php

namespace App\Events;

use App\Models\Site;
use App\Models\ProjectNote;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectNoteUpdateEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $site;
    public $projectNote;
    public $notifyUser;

    public function __construct(Site $site, ProjectNote $projectNote, $notifyUser)
    {
        $this->site = $site;
        $this->projectNote = $projectNote;
        $this->notifyUser = $notifyUser;
    }
}
