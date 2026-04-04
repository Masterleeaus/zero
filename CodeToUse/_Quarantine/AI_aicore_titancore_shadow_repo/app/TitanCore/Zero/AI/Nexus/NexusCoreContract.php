<?php

namespace App\TitanCore\Zero\AI\Nexus;

interface NexusCoreContract
{
    public function key(): string;

    /**
     * @param  array<string, mixed>  $contextPack
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function evaluate(array $contextPack, array $options = []): array;
}
