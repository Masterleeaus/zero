<?php

namespace App\TitanCore\Zero\Signals;

use App\Titan\Signals\EnvelopeBuilder;
use App\Titan\Signals\SignalsService;

class SignalBridge
{
    public function __construct(
        protected SignalsService $signals,
        protected EnvelopeBuilder $envelopeBuilder,
    ) {
    }

    /**
     * @param  array<string, mixed>  $processPayload
     * @param  array<string, mixed>  $signalPayload
     * @return array<string, mixed>
     */
    public function recordAndPublish(array $processPayload, array $signalPayload = []): array
    {
        return $this->signals->recordAndIngest($processPayload, $signalPayload);
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function envelope(array $context = []): array
    {
        return $this->envelopeBuilder->build($context);
    }
}
