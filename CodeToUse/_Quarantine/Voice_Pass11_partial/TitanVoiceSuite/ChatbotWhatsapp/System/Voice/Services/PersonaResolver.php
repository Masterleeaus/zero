<?php

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;

class PersonaResolver
{
    public function __construct(
        protected LifecycleStageResolver $stageResolver,
    ) {}

    public function resolve(ChatbotConversation $conversation, string $channel, ?string $intent = null): string
    {
        $personas = (array) config('titan-personas.personas', []);
        $stage = $this->stageResolver->resolve($intent);

        foreach ($personas as $key => $persona) {
            $channels = (array) ($persona['channels'] ?? []);
            $stages = (array) ($persona['stages'] ?? []);
            $intents = (array) ($persona['intents'] ?? []);
            $metaHint = (string) ($persona['conversation_name_contains'] ?? '');

            $channelMatch = in_array($channel, $channels, true);
            $stageMatch = $stages === [] || in_array($stage, $stages, true);
            $intentMatch = $intent === null || $intents === [] || in_array($intent, $intents, true);
            $nameMatch = $metaHint === '' || str_contains((string) ($conversation->conversation_name ?? ''), $metaHint);

            if ($channelMatch && $stageMatch && $intentMatch && $nameMatch) {
                return (string) $key;
            }
        }

        if ($channel === 'voice' && in_array($stage, ['dispatch', 'field_work', 'completion'], true)) {
            return 'go';
        }

        if ($channel === 'voice' && in_array($stage, ['quote', 'invoice', 'planning'], true)) {
            return 'command';
        }

        return (string) config('titan-personas.default', 'nexus');
    }
}
