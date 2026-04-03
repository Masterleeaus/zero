<?php

return [
    'channels' => [
        'sms' => ['enabled' => true, 'format' => 'text'],
        'voice' => ['enabled' => true, 'format' => 'twiml'],
        'whatsapp' => ['enabled' => true, 'format' => 'text'],
    ],
    'routing' => [
        'voice_commands' => true,
        'ivr_menu' => true,
        'ai_fallback' => true,
        'agent_transfer' => true,
    ],
    'features' => [
        'voicemail' => true,
        'callbacks' => true,
        'recording' => true,
    ],
];
