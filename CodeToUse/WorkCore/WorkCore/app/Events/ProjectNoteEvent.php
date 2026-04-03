<?php

namespace App\Events;

use App\Models\Site;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectNoteEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $site;
    public $unmentionUser;
    public $created_at;

    public function __construct(Site $site, $created_at, $unmentionUser)
    {
        $this->site = $site;
        $this->created_at = $created_at;
        $this->unmentionUser = $unmentionUser;
    }

}
