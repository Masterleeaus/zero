<?php

namespace Modules\Documents\DTO;

class WorkflowAction
{
    public function __construct(
        public string $label,
        public string $route,
        public array $routeParams = [],
        public string $method = 'POST',
        public string $style = 'outline',
    ) {
    }
}
