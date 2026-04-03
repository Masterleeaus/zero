<?php

namespace Modules\TitanHello\Services\Providers\Twilio;

class TwilioSignatureValidator
{
    public function validate(string $authToken, ?string $signature, string $url, array $params): bool
    {
        if (!$authToken || !$signature) {
            return false;
        }

        ksort($params);
        $data = $url;
        foreach ($params as $k => $v) {
            $data .= $k . $v;
        }

        $computed = base64_encode(hash_hmac('sha1', $data, $authToken, true));
        return hash_equals($computed, $signature);
    }
}
