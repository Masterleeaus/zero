<?php

namespace Modules\Workflow\Conditions\Operators;

use Modules\Workflow\Conditions\Contracts\ConditionInterface;
use Modules\Workflow\Conditions\Payload\PayloadAccessor;

class GreaterThan implements ConditionInterface
{
    public function key(): string { return 'gt'; }
    public function label(): string { return 'Greater Than'; }

    public function evaluate(array $payload, array $config = []): bool
    {
        $path = (string)($config['path'] ?? '');
        $value = $config['value'] ?? null;
        $cur = PayloadAccessor::get($payload, $path);
        return is_numeric($cur) && is_numeric($value) && $cur > $value;

    }

    public function configSchema(): array
    {
        return ['path'=>'string','value'=>'number'];
    }
}
