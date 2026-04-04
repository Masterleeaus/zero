<?php

return [
    'telegram' => [
        'extension' => 'ChatbotTelegram',
        'enabled' => true,
        'ingest_event' => 'chatbot.telegram.inbound',
        'send_event' => 'chatbot.telegram.outbound',
    ],
    'whatsapp' => [
        'extension' => 'ChatbotWhatsapp',
        'enabled' => true,
        'ingest_event' => 'chatbot.whatsapp.inbound',
        'send_event' => 'chatbot.whatsapp.outbound',
    ],
    'messenger' => [
        'extension' => 'ChatbotMessenger',
        'enabled' => true,
        'ingest_event' => 'chatbot.messenger.inbound',
        'send_event' => 'chatbot.messenger.outbound',
    ],
    'voice' => [
        'extension' => 'ChatbotVoice',
        'enabled' => true,
        'ingest_event' => 'chatbot.voice.inbound',
        'send_event' => 'chatbot.voice.outbound',
    ],
    'agent' => [
        'extension' => 'ChatbotAgent',
        'enabled' => true,
        'ingest_event' => 'chatbot.agent.inbound',
        'send_event' => 'chatbot.agent.outbound',
    ],
];
