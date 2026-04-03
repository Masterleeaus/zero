<?php

namespace Modules\Workflow\Conditions\Operators;

use Modules\Workflow\Conditions\Contracts\ConditionInterface;
use Modules\Workflow\Conditions\Payload\PayloadAccessor;

class Equals implements ConditionInterface
{
    public function key(): string { return 'equals'; }
    public function label(): string { return 'Equals'; }

    public function evaluate(array $payload, array $config = []): bool
    {
        $path = (string)($config['path'] ?? '');
        $value = $config['value'] ?? null;
        return PayloadAccessor::get($payload, $path) == $value;

    }

    public function configSchema(): array
    {
        return ['path'=>'string','value'=>'mixed'];
    }
}
