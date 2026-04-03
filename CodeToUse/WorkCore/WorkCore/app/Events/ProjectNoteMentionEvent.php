<?php

namespace App\Events;

use App\Models\Site;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectNoteMentionEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $site;
    public $mentionuser;
    public $created_at;

    public function __construct(Site $site, $created_at, $mentionuser)
    {

        $this->site = $site;
        $this->created_at = $created_at;
        $this->mentionuser = $mentionuser;

    }

}
