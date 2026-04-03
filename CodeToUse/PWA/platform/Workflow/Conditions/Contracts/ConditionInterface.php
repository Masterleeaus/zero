<?php

namespace Modules\Workflow\Conditions\Contracts;

interface ConditionInterface
{
    public function key(): string;
    public function label(): string;

    /**
     * Evaluate condition against payload.
     * @param array $payload
     * @param array $config
     */
    public function evaluate(array $payload, array $config = []): bool;

    /**
     * Configuration schema for UI.
     */
    public function configSchema(): array;
}
