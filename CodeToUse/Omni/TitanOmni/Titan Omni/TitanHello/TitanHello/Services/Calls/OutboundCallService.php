<?php

namespace Modules\TitanHello\Services\Calls;

use Illuminate\Contracts\Auth\Authenticatable;
use Modules\TitanHello\Models\Call;
use Modules\TitanHello\Services\Providers\Twilio\TwilioProvider;

/**
 * Outbound calling facade.
 *
 * Titan Hello is phone-only.
 * - It stores calls + provider events/recordings
 * - It can optionally request transcription/summary via Titan Zero AFTER the call (future pass)
 */
class OutboundCallService
{
    public function __construct(protected TwilioProvider $twilio)
    {
    }

    /**
     * Backwards compatible helper.
     */
    public function placeCall(string $toNumber, string $fromNumber = ''): Call
    {
        return $this->dialNumber(0, $fromNumber ?: '', $toNumber, null, []);
    }

    /**
     * Canonical outbound dial.
     *
     * @param array<string,mixed> $meta
     */
    public function dialNumber(int $companyId, ?string $fromNumber, string $toNumber, ?Authenticatable $user, array $meta = []): Call
    {
        // Persist local call row first (so Inbox shows it immediately).
        $call = Call::create([
            'company_id' => $companyId,
            'direction' => 'outbound',
            'from_number' => $fromNumber ?: null,
            'to_number' => $toNumber,
            'status' => 'queued',
            'assigned_to_user_id' => $user?->id,
            'meta' => $meta,
        ]);

        // Provider dial (Twilio first). If not configured, keep call queued.
        try {
            $providerSid = $this->twilio->createOutboundCall($toNumber, $fromNumber ?: null, [
                'statusCallback' => route('titanhello.webhooks.voice.status'),
            ]);

            $call->provider = 'twilio';
            $call->provider_call_sid = $providerSid;
            $call->status = 'dialing';
            $call->save();
        } catch (\Throwable $e) {
            $call->status = 'failed';
            $call->save();
        }

        return $call;
    }
}
