<?php

namespace Extensions\TitanHello\Services;

use Illuminate\Support\Facades\Http;
use Extensions\TitanHello\Services\SettingsService;

class TwilioSmsService
{
    public function send(string $to, string $message): bool
    {
        $sid = (string) app(SettingsService::class)->get('twilio.account_sid', '');
        $token = (string) app(SettingsService::class)->get('twilio.auth_token', '');
        $from = (string) app(SettingsService::class)->get('twilio.sms_from_number', app(SettingsService::class)->get('twilio.default_number', ''));

        if ($sid === '' || $token === '' || $from === '' || $to === '' || $message === '') {
            return false;
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

        $resp = Http::asForm()
            ->withBasicAuth($sid, $token)
            ->timeout(10)
            ->post($url, [
                'From' => $from,
                'To' => $to,
                'Body' => $message,
            ]);

        return $resp->successful();
    }
}
