<?php

namespace Modules\TitanHello\Services\Providers\Twilio;

/**
 * Minimal Twilio REST API client using cURL.
 *
 * This keeps Titan Hello lightweight: no vendored SDK copies.
 */
class TwilioRestApiClient
{
    protected string $baseUrl;
    protected string $accountSid;
    protected string $authToken;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('titanhello.twilio.base_url', 'https://api.twilio.com/2010-04-01/Accounts'), '/');
        $this->accountSid = (string) config('titanhello.twilio.account_sid');
        $this->authToken = (string) config('titanhello.twilio.auth_token');
    }

    /**
     * @param array<string,mixed> $fields
     * @return array<string,mixed>
     */
    public function post(string $path, array $fields): array
    {
        $url = $this->baseUrl . '/' . $this->accountSid . '/' . ltrim($path, '/');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->accountSid . ':' . $this->authToken);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        $resp = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($resp === false) {
            throw new \RuntimeException('Twilio REST error: ' . $err);
        }

        $data = json_decode($resp, true);
        if ($code >= 400) {
            throw new \RuntimeException('Twilio REST HTTP ' . $code . ': ' . ($data['message'] ?? $resp));
        }

        return is_array($data) ? $data : [];
    }
}
