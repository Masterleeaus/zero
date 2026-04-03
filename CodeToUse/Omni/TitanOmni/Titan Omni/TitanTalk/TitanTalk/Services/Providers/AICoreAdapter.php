<?php

namespace Modules\TitanTalk\Services\Providers;

use Illuminate\Support\Facades\App;
use Modules\TitanTalk\Support\UsageLedger;
use Modules\TitanCore\Contracts\AI\ClientInterface as TitanCoreClient;

class AICoreAdapter implements ProviderInterface
{
    public function reply(string $message, array $context = []): array
    {
        $message = (string) $message;

        // Channel + policy hints coming from Titan Talk
        $channel       = $context['channel'] ?? 'web';
        $policyProfile = $context['policy_profile'] ?? 'assistant';

        $config        = config('titantalk', []);
        $provider      = $config['provider'] ?? null;
        $channelModels = $config['channel_models'] ?? [];
        $defaultModel  = $config['model'] ?? null;

        // Per-channel model override if configured
        $modelOverride = $channelModels[$channel] ?? $defaultModel;

        // Choose system prompt based on policy profile
        if ($policyProfile === 'compliance') {
            $system = 'You are Titan Talk – Compliance Mode. '
                    . 'You help with construction and trades compliance. '
                    . 'You are strict, conservative, and never guess. If you are '
                    . 'not certain about a regulation, you clearly say you are not certain '
                    . 'and suggest checking the official standard or a qualified professional. '
                    . 'You avoid giving legal advice and instead explain general principles.';
        } else {
            $system = 'You are Titan Talk, a multi-channel assistant for trades and service '
                    . 'businesses. You reply concisely, ask clarifying questions when needed, '
                    . 'and avoid hallucinating facts.';
        }

        try {
            /** @var TitanCoreClient $client */
            $client = App::make(TitanCoreClient::class);

            // Titan Core expects array-of-messages
            $messages = [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $message],
            ];

            $options = [
                'module_alias'   => 'titan-talk',
                'model'          => $modelOverride,   // can be null → Titan Core default
                'provider'       => $provider,        // can be null → Titan Core default
                'meta'           => $context,
                'channel'        => $channel,
                'policy_profile' => $policyProfile,
            ];

            $result = $client->chat($messages, $options);

            // Extract text reply from Titan Core result
            $text = (string) (
                $result['response']['content']
                ?? $result['response']['text']
                ?? $result['text']
                ?? ''
            );

            if ($text === '') {
                $text = '[TitanCore] Empty response';
            }

            // Record usage (if Titan Core returned tokens/cost)
            if (isset($result['tokens']) || isset($result['cost'])) {
                UsageLedger::record(
                    'titan-talk',
                    strlen($message),
                    strlen($text),
                    [
                        'conversation_id' => $context['conversation_id'] ?? null,
                        'direction'       => 'response',
                        'provider'        => $result['provider'] ?? null,
                        'model'           => $result['model'] ?? null,
                        'tokens'          => $result['tokens'] ?? null,
                        'cost'            => $result['cost'] ?? null,
                    ]
                );
            } else {
                UsageLedger::record(
                    'titan-talk',
                    strlen($message),
                    strlen($text),
                    [
                        'conversation_id' => $context['conversation_id'] ?? null,
                        'direction'       => 'response',
                    ]
                );
            }

            return [
                'text' => $text,
                'meta' => [
                    'provider'       => $result['provider'] ?? 'titan-core',
                    'model'          => $result['model'] ?? null,
                    'tokens'         => $result['tokens'] ?? null,
                    'cost'           => $result['cost'] ?? null,
                    'raw'            => $result,
                    'policy_profile' => $policyProfile,
                ],
            ];
        } catch (\Throwable $e) {
            $text = '[TitanCore error] ' . $e->getMessage();

            UsageLedger::record(
                'titan-talk',
                strlen($message),
                strlen($text),
                [
                    'conversation_id' => $context['conversation_id'] ?? null,
                    'direction'       => 'response-error',
                ]
            );

            return [
                'text' => $text,
                'meta' => [
                    'provider' => 'titan-core',
                    'error'    => [
                        'type'    => 'exception',
                        'message' => $e->getMessage(),
                    ],
                ],
            ];
        }
    }
}
