<?php

namespace App\TitanCore\Zero\Process;

use App\Titan\Signals\ProcessRecorder;
use App\Titan\Signals\ProcessStateMachine;

class ProcessBridge
{
    public function __construct(
        protected ProcessRecorder $recorder,
        protected ProcessStateMachine $stateMachine,
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function record(array $payload): array
    {
        return $this->recorder->record($payload);
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function transition(string $processId, string $state, array $metadata = []): array
    {
        return $this->stateMachine->transitionState($processId, $state, $metadata);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function map(): array
    {
        return $this->stateMachine->transitions();
    }
}
