<?php

namespace Modules\TitanHello\Services\TitanZero;

use Illuminate\Support\Facades\Http;

class TitanZeroClient
{
    public function requestVoicemailSummary(array $payload): ?array
    {
        $endpoint = (string) config('titanhello.titan_zero.endpoint', '');
        if (!$endpoint) {
            return null;
        }

        $resp = Http::timeout(60)->post($endpoint, $payload);
        if (!$resp->ok()) {
            return null;
        }

        return $resp->json();
    }
}
