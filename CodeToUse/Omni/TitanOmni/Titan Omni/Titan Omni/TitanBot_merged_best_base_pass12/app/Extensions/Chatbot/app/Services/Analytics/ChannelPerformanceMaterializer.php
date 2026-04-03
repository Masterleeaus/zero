<?php

namespace App\Extensions\Chatbot\App\Services\Analytics;

class ChannelPerformanceMaterializer
{
    public function build(): array
    {
        return [
            'telegram' => ['conversations' => 0, 'avg_response_time' => 0],
            'whatsapp' => ['conversations' => 0, 'avg_response_time' => 0],
            'messenger' => ['conversations' => 0, 'avg_response_time' => 0],
            'voice' => ['conversations' => 0, 'avg_response_time' => 0],
            'agent' => ['conversations' => 0, 'avg_response_time' => 0],
        ];
    }
}
