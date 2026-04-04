<?php

namespace App\TitanCore\Registry\Tools;

class ToolDefinition
{
    /**
     * @param  array<int, string>  $surfaces
     */
    public function __construct(
        public string $key,
        public string $label,
        public string $handler,
        public array $surfaces = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'handler' => $this->handler,
            'surfaces' => $this->surfaces,
        ];
    }
}
