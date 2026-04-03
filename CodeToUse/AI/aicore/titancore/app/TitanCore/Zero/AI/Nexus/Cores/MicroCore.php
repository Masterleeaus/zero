<?php

namespace App\TitanCore\Zero\AI\Nexus\Cores;

use App\TitanCore\Zero\AI\Nexus\AbstractNexusCore;

class MicroCore extends AbstractNexusCore
{
    public function key(): string
    {
        return 'micro';
    }

    protected function summary(array $contextPack, array $options = []): string
    {
        return 'Provide a micro-level near-term action assessment for the current envelope.';
    }

    protected function recommendations(array $contextPack, array $options = []): array
    {
        return ['Identify next action', 'List missing local details'];
    }
}
