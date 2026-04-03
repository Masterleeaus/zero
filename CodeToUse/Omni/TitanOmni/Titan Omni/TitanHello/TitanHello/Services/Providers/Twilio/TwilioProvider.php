<?php

namespace Modules\TitanHello\Services\Providers\Twilio;

use Modules\TitanHello\Services\Providers\PhoneProviderInterface;

class TwilioProvider implements PhoneProviderInterface
{
    public function __construct(protected TwilioRestApiClient $client)
    {
    }

    public function providerKey(): string
    {
        return 'twilio';
    }

    public function validateSignature(array $headers, string $url, array $payload): bool
    {
        $token = (string) config('titanhello.twilio.auth_token');
        $sig = null;

        foreach (['x-twilio-signature', 'X-Twilio-Signature', 'X_TWILIO_SIGNATURE'] as $k) {
            if (isset($headers[$k])) {
                $v = $headers[$k];
                $sig = is_array($v) ? ($v[0] ?? null) : $v;
                break;
            }
        }

        $validator = new TwilioSignatureValidator();
        return $validator->validate($token, (string) $sig, $url, $payload);
    }

    public function mapInbound(array $payload): array
    {
        $mapper = new TwilioWebhookMapper();
        return $mapper->inbound($payload);
    }

    public function mapStatus(array $payload): array
    {
        $mapper = new TwilioWebhookMapper();
        return $mapper->status($payload);
    }

    public function mapRecording(array $payload): array
    {
        $mapper = new TwilioWebhookMapper();
        return $mapper->recording($payload);
    }

    /**
     * Create an outbound call via Twilio REST API.
     *
     * @param array<string,mixed> $options
     */
    public function createOutboundCall(string $toNumber, ?string $fromNumber, array $options = []): string
    {
        $from = $fromNumber ?: (string) config('titanhello.twilio.from_number');
        $twimlUrl = $options['twimlUrl'] ?? (string) config('titanhello.twilio.outbound_twiml_url');

        // fallback: use built-in outbound twiml endpoint if set
        if (!$twimlUrl) {
            $twimlUrl = route('titanhello.webhooks.voice.outbound_twiml');
        }

        $payload = [
            'To' => $toNumber,
            'From' => $from,
            'Url' => $twimlUrl,
        ];

        if (!empty($options['statusCallback'])) {
            $payload['StatusCallback'] = $options['statusCallback'];
            $payload['StatusCallbackEvent'] = ['initiated', 'ringing', 'answered', 'completed'];
            $payload['StatusCallbackMethod'] = 'POST';
        }

        $res = $this->client->post("/Calls.json", $payload);

        return (string) ($res['sid'] ?? '');
    }
}
