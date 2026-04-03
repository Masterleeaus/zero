<?php
namespace Modules\TitanTalk\GraphQL\Resolvers;

use Modules\TitanTalk\Models\Conversation;

class ConversationResolver {
    public function all($_, array $args){ return Conversation::orderBy('id','desc')->limit(100)->get(); }
}
