<?php

namespace App\TitanCore\Zero\AI\Nexus\Cores;

use App\TitanCore\Zero\AI\Nexus\AbstractNexusCore;

class CreatorCore extends AbstractNexusCore
{
    public function key(): string
    {
        return 'creator';
    }

    protected function summary(array $contextPack, array $options = []): string
    {
        return 'Provide a creative and interface assessment for the current envelope.';
    }

    protected function recommendations(array $contextPack, array $options = []): array
    {
        return ['Suggest UI framing', 'Summarize end-user clarity risks'];
    }
}
