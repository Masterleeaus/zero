<?php

namespace Modules\Workflow\Conditions\Operators;

use Modules\Workflow\Conditions\Contracts\ConditionInterface;
use Modules\Workflow\Conditions\Payload\PayloadAccessor;

class DateWithin implements ConditionInterface
{
    public function key(): string { return 'date_within'; }
    public function label(): string { return 'Date Within (days)'; }

    public function evaluate(array $payload, array $config = []): bool
    {
        $path = (string)($config['path'] ?? '');
        $days = (int)($config['days'] ?? 0);
        $val = PayloadAccessor::get($payload, $path);

        if (!$val) return false;

        $ts = strtotime((string)$val);
        if (!$ts) return false;

        $delta = abs(time() - $ts);
        return $delta <= ($days * 86400);
    }

    public function configSchema(): array
    {
        return ['path'=>'string','days'=>'int'];
    }
}
