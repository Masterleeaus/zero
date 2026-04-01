<?php

namespace App\TitanCore\Zero\Memory;

class MemorySnapshot
{
    /**
     * @param  array<string, mixed>  $items
     */
    public function __construct(
        public string $key,
        public array $items = [],
        public string $scope = 'tenant',
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'scope' => $this->scope,
            'items' => $this->items,
        ];
    }
}
