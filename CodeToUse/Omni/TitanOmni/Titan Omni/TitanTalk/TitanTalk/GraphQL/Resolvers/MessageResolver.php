<?php
namespace Modules\TitanTalk\GraphQL\Resolvers;

use Modules\TitanTalk\Models\Message;

class MessageResolver {
    public function byConversation($_, array $args){ return Message::where('conversation_id', $args['conversation_id'])->orderBy('id')->limit(500)->get(); }
}
