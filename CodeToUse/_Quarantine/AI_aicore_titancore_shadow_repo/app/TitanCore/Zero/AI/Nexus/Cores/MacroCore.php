<?php

namespace App\TitanCore\Zero\AI\Nexus\Cores;

use App\TitanCore\Zero\AI\Nexus\AbstractNexusCore;

class MacroCore extends AbstractNexusCore
{
    public function key(): string
    {
        return 'macro';
    }

    protected function summary(array $contextPack, array $options = []): string
    {
        return 'Provide a macro-level long-range impact assessment for the current envelope.';
    }

    protected function recommendations(array $contextPack, array $options = []): array
    {
        return ['Check downstream effects', 'Surface strategic implications'];
    }
}
