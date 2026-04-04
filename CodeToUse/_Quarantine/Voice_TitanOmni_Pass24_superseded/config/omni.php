<?php

return [
    'default_channel' => env('OMNI_DEFAULT_CHANNEL', 'web'),
    'allowed_channels' => ['web', 'internal', 'api', 'whatsapp', 'telegram', 'messenger', 'voice'],
    'dual_write_legacy' => env('OMNI_DUAL_WRITE_LEGACY', true),
    'keep_channel_extensions_separate' => true,
];
