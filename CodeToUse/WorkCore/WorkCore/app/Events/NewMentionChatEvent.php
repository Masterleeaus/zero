<?php

namespace App\Events;

use App\Models\UserChat;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Service Agreements\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMentionChatEvent implements ShouldBroadcast
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userChat;
    public $notifyUser;

    public function __construct(UserChat $userChat, $notifyUser)
    {
        $this->userChat = $userChat;
        $this->notifyUser = $notifyUser;

    }

    public function broadcastOn()
    {
        return ['team chat-channel'];
    }

    public function broadcastAs()
    {
        return 'team chat.received';
    }

}
