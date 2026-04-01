<?php

namespace App\TitanCore\Registry\Runtime;

class RuntimeDefinition
{
    /**
     * @param  array<int, string>  $capabilities
     */
    public function __construct(
        public string $key,
        public string $label,
        public string $adapter,
        public array $capabilities = [],
        public bool $enabled = true,
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
            'adapter' => $this->adapter,
            'capabilities' => $this->capabilities,
            'enabled' => $this->enabled,
        ];
    }
}
