<?php

namespace Modules\Documents\DTO;

class WorkflowTransition
{
    public function __construct(
        public string $from,
        public string $to,
        public ?string $label = null,
        public ?string $permission = null,
    ) {
    }
}
