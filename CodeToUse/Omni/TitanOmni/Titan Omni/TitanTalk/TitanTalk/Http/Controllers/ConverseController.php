<?php

namespace Modules\TitanTalk\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanTalk\Models\Conversation;
use Modules\TitanTalk\Models\Message;
use Modules\TitanTalk\Services\Providers\ProviderInterface;

class ConverseController extends Controller
{
    public function __construct(protected ProviderInterface $provider) {}

    public function send(Request $req)
    {
        $text = (string) $req->input('message','');
        $convId = $req->input('conversation_id');
        $conv = $convId ? Conversation::find($convId) : Conversation::create(['tenant_id'=>null,'channel'=>'web']);
        if(!$conv) { $conv = Conversation::create(['tenant_id'=>null,'channel'=>'web']); }

        Message::create([ 'tenant_id'=>null, 'conversation_id'=>$conv->id, 'sender'=>'user', 'text'=>$text, 'meta'=>[] ]);
        $resp = $this->provider->reply($text, ['conversation_id'=>$conv->id]);
        Message::create([ 'tenant_id'=>null, 'conversation_id'=>$conv->id, 'sender'=>'bot', 'text'=>$resp['text'] ?? '', 'meta'=>$resp['meta'] ?? [] ]);

        return response()->json(['conversation_id'=>$conv->id,'reply'=>$resp], 200);
    }
}
