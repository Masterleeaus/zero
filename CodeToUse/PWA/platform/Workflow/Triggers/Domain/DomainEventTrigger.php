<?php

namespace Modules\Workflow\Triggers\Domain;

use Modules\Workflow\Triggers\Contracts\TriggerInterface;

class DomainEventTrigger implements TriggerInterface
{
    public function __construct(
        protected string $key,
        protected string $label = '',
        protected array $schema = [],
        protected array $sample = []
    ) {}

    public function key(): string { return $this->key; }

    public function label(): string { return $this->label ?: $this->key; }

    public function schema(): array { return $this->schema; }

    public function sample(): array { return $this->sample; }
}
