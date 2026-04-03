<?php

namespace Modules\Workflow\Conditions\Operators;

use Modules\Workflow\Conditions\Contracts\ConditionInterface;
use Modules\Workflow\Conditions\Payload\PayloadAccessor;

class Contains implements ConditionInterface
{
    public function key(): string { return 'contains'; }
    public function label(): string { return 'Contains'; }

    public function evaluate(array $payload, array $config = []): bool
    {
        $path = (string)($config['path'] ?? '');
        $needle = (string)($config['value'] ?? '');
        $hay = PayloadAccessor::get($payload, $path);
        if (is_array($hay)) return in_array($needle, $hay, true);
        if (is_string($hay)) return str_contains($hay, $needle);
        return false;

    }

    public function configSchema(): array
    {
        return ['path'=>'string','value'=>'string'];
    }
}
