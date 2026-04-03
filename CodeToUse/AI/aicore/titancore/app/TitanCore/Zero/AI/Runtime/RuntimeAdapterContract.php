<?php

namespace App\TitanCore\Zero\AI\Runtime;

interface RuntimeAdapterContract
{
    public function key(): string;

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function execute(string $instruction, array $context = []): array;
}
