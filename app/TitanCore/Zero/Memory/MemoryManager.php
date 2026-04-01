<?php

namespace App\TitanCore\Zero\Memory;

use App\TitanCore\Zero\Memory\Session\SessionHandoffManager;

class MemoryManager
{
    public function __construct(
        protected SessionHandoffManager $handoffManager,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(string $key): array
    {
        return (new MemorySnapshot($key, [
            'session' => $this->handoffManager->export($key),
            'scope' => 'tenant',
            'status' => 'deferred',
        ]))->toArray();
    }
}
