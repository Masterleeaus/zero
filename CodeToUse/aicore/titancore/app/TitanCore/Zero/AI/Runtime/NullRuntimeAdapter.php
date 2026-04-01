<?php

namespace App\TitanCore\Zero\AI\Runtime;

class NullRuntimeAdapter implements RuntimeAdapterContract
{
    public function key(): string
    {
        return 'null';
    }

    public function execute(string $instruction, array $context = []): array
    {
        return [
            'runtime' => $this->key(),
            'instruction' => $instruction,
            'context' => $context,
            'status' => 'deferred',
            'message' => 'Titan Zero runtime adapter not configured yet.',
        ];
    }
}
