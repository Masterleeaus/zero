<?php

namespace Modules\Workflow\Conditions\Operators;

use Modules\Workflow\Conditions\Contracts\ConditionInterface;
use Modules\Workflow\Conditions\Payload\PayloadAccessor;

class IsEmpty implements ConditionInterface
{
    public function key(): string { return 'empty'; }
    public function label(): string { return 'Is Empty'; }

    public function evaluate(array $payload, array $config = []): bool
    {
        $path = (string)($config['path'] ?? '');
        $cur = PayloadAccessor::get($payload, $path);
        if (is_null($cur)) return true;
        if (is_string($cur)) return trim($cur) === '';
        if (is_array($cur)) return count($cur) === 0;
        return false;

    }

    public function configSchema(): array
    {
        return ['path'=>'string'];
    }
}
