<?php

return [
    'twilio' => [
        'require_signature' => env('TITANHELLO_TWILIO_REQUIRE_SIGNATURE', false),
        'auth_token' => env('TITANHELLO_TWILIO_AUTH_TOKEN', null),
        'account_sid' => env('TITANHELLO_TWILIO_ACCOUNT_SID', null),
        'from_number' => env('TITANHELLO_TWILIO_FROM_NUMBER', null),
        'base_url' => env('TITANHELLO_TWILIO_BASE_URL', 'https://api.twilio.com/2010-04-01/Accounts'),
        'outbound_twiml_url' => env('TITANHELLO_TWILIO_OUTBOUND_TWIML_URL', null),
    ],

'recordings' => [
    'disk' => env('TITANHELLO_RECORDINGS_DISK', 'local'),
    'path' => env('TITANHELLO_RECORDINGS_PATH', 'titanhello/recordings'),
    'retention_days' => (int) env('TITANHELLO_RECORDINGS_RETENTION_DAYS', 30),
],

'voicemail' => [
    'request_titan_zero_summary' => (bool) env('TITANHELLO_VOICEMAIL_TITAN_ZERO_SUMMARY', false),
],

'titan_zero' => [
    // Optional: internal endpoint that Titan Zero exposes to accept structured requests.
    'endpoint' => env('TITANHELLO_TITAN_ZERO_ENDPOINT', ''),
],

];
