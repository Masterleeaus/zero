<?php
namespace Modules\TitanTalk\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanTalk\Models\Conversation;
use Modules\TitanTalk\Models\Message;
use Modules\TitanTalk\Models\Channel;
use Modules\TitanTalk\Services\Providers\ProviderInterface;
use Modules\TitanTalk\Services\CrmLinker;

class WebhookController extends Controller
{
    protected ProviderInterface $provider;
    protected CrmLinker $crmLinker;

    public function __construct(ProviderInterface $provider, CrmLinker $crmLinker)
    {
        $this->provider  = $provider;
        $this->crmLinker = $crmLinker;
    }

    // Generic webhook receiver: /api/titantalk/hook/{driver}
    public function receive(Request $req, string $driver)
    {
        $channelModel = Channel::where('driver', $driver)->where('enabled', 1)->first();
        if (! $channelModel) {
            return response()->json(['error' => 'channel-disabled'], 404);
        }

        // Normalize inbound payload (simplified demo)
        $text     = (string) ($req->input('text') ?? $req->input('message') ?? '');
        $external = (string) ($req->input('from') ?? $req->input('chat_id') ?? 'unknown');

        $conv = Conversation::firstOrCreate(
            ['external_ref' => $external, 'channel' => $driver],
            ['tenant_id' => null]
        );

        // Store user message
        Message::create([
            'tenant_id'       => null,
            'conversation_id' => $conv->id,
            'sender'          => 'user',
            'text'            => $text,
            'meta'            => $req->all(),
        ]);

        // Attempt basic CRM auto-linking
        $this->crmLinker->linkConversation($conv);

        // Determine policy profile based on routing config
        $config  = config('titantalk.routing', []);
        $defaults = $config['defaults'] ?? [];
        $numbers  = $config['numbers'] ?? [];

        $policyProfile = $defaults[$driver]['policy_profile'] ?? 'assistant';

        if (isset($numbers[$driver . ':' . $external])) {
            $policyProfile = $numbers[$driver . ':' . $external];
        }

        // Ask Titan Core via AICoreAdapter
        $resp = $this->provider->reply($text, [
            'conversation_id' => $conv->id,
            'channel'         => $driver,
            'policy_profile'  => $policyProfile,
            'external_ref'    => $external,
        ]);

        // Store bot message
        Message::create([
            'tenant_id'       => null,
            'conversation_id' => $conv->id,
            'sender'          => 'bot',
            'text'            => $resp['text'] ?? '',
            'meta'            => $resp['meta'] ?? [],
        ]);

        // Basic echo back response; in real adapters you'd call the channel API to send a message back
        return response()->json(['ok' => true, 'reply' => $resp], 200);
    }
}
