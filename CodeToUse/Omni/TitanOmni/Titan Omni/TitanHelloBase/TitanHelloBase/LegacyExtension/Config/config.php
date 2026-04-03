<?php

return [
    'version' => 1.0,

    // Telephony defaults (overridable in admin settings)
    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'default_number' => env('TWILIO_PHONE_NUMBER'),
    ],

    'routing' => [
        'timezone' => env('TITAN_HELLO_TIMEZONE', 'Australia/Melbourne'),
        // take_message | forward
        'after_hours_mode' => env('TITAN_HELLO_AFTER_HOURS_MODE', 'take_message'),
        'forward_number' => env('TITAN_HELLO_FORWARD_NUMBER'),
        // Optional: can be expanded later into per-day ranges
        'business_hours' => [
            'mon' => ['start' => '07:00', 'end' => '17:00'],
            'tue' => ['start' => '07:00', 'end' => '17:00'],
            'wed' => ['start' => '07:00', 'end' => '17:00'],
            'thu' => ['start' => '07:00', 'end' => '17:00'],
            'fri' => ['start' => '07:00', 'end' => '17:00'],
            'sat' => ['start' => '08:00', 'end' => '12:00'],
            'sun' => ['start' => null, 'end' => null],
        ],
        'voicemail' => [
            'max_length' => (int) env('TITAN_HELLO_VOICEMAIL_MAX_LENGTH', 120),
            'play_beep' => (bool) env('TITAN_HELLO_VOICEMAIL_BEEP', true),
        ],
    ],

    'security' => [
        'verify_twilio_signature' => (bool) env('TITAN_HELLO_VERIFY_TWILIO_SIGNATURE', true),
        'bridge_shared_secret' => env('TITAN_HELLO_BRIDGE_SHARED_SECRET'),
    ],

    'recording' => [
        'enabled' => (bool) env('TITAN_HELLO_RECORDING_ENABLED', false),
        'consent_message' => env('TITAN_HELLO_CONSENT_MESSAGE', 'This call may be recorded for quality and proof of work.'),
    ],

    // ElevenLabs Conversational AI settings.
    // Auth and API base URLs are handled by the app-level ElevenLabsService.
    'elevenlabs' => [
        // Placeholder toggles for the realtime bridge.
        'realtime_enabled' => (bool) env('TITAN_HELLO_ELEVENLABS_REALTIME', true),
        // API key is used by the WS bridge to obtain signed URLs (private agents).
        // You can also store this via admin settings (recommended): elevenlabs.api_key
        'api_key' => env('ELEVENLABS_API_KEY'),

        // Twilio Media Streams uses μ-law 8kHz by default; ElevenLabs supports ulaw_8000.
        // These can be overridden via admin settings.
        'user_input_audio_format' => env('TITAN_HELLO_USER_INPUT_AUDIO_FORMAT', 'ulaw_8000'),
        'agent_output_audio_format' => env('TITAN_HELLO_AGENT_OUTPUT_AUDIO_FORMAT', 'ulaw_8000'),

        // If you run a dedicated bridge host, set it here for UI display/debug only.
        'bridge_note' => env('TITAN_HELLO_BRIDGE_NOTE', 'Proxy wss://<your-domain>/api/titan-hello/twilio/voice/stream to the bridge daemon.'),
    ],

    // WebSocket bridge settings.
    // Twilio connects to the public URL; your reverse proxy forwards it to the daemon.
    'bridge' => [
        // Public ws/wss URL that Twilio should connect to.
        'public_ws_url' => env('TITAN_HELLO_PUBLIC_WS_URL'),
        // Internal bind host/port for the daemon.
        'host' => env('TITAN_HELLO_BRIDGE_HOST', '127.0.0.1'),
        'port' => (int) env('TITAN_HELLO_BRIDGE_PORT', 8081),
    ],

    'avatars' => [
        'avatar-1.png',
    ],

    'followup' => [
        'sms_enabled' => false,
        'sms_template' => "Thanks for calling. We've received your enquiry and will get back to you shortly.",
    ],


];
