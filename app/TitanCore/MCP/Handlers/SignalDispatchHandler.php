<?php

namespace App\TitanCore\MCP\Handlers;

use App\Titan\Signals\SignalsService;

class SignalDispatchHandler
{
    public function __construct(protected SignalsService $signals)
    {
    }

    /** @param array<string, mixed> $params */
    public function handle(array $params): array
    {
        return $this->signals->recordAndIngest(
            $params['process_payload'] ?? $params,
            $params['signal_payload']  ?? [],
        );
    }
}
