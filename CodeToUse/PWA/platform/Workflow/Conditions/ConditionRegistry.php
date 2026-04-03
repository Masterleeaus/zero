<?php

namespace Modules\Workflow\Conditions;

use Modules\Workflow\Conditions\Contracts\ConditionInterface;

class ConditionRegistry
{
    /** @var array<string,ConditionInterface> */
    protected array $conditions = [];

    public function register(ConditionInterface $condition): void
    {
        $this->conditions[$condition->key()] = $condition;
    }

    /** @return array<string,ConditionInterface> */
    public function all(): array
    {
        return $this->conditions;
    }

    public function get(string $key): ?ConditionInterface
    {
        return $this->conditions[$key] ?? null;
    }

    public function evaluateAll(array $payload, array $conditions): bool
    {
        foreach ($conditions as $cond) {
            $key = $cond['key'] ?? '';
            $config = $cond['config'] ?? [];
            $handler = $this->get($key);
            if (!$handler) {
                return false;
            }
            if (!$handler->evaluate($payload, $config)) {
                return false;
            }
        }
        return true;
    }
}
