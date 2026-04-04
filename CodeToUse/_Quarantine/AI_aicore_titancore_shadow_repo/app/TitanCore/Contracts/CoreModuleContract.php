<?php

namespace App\TitanCore\Contracts;

interface CoreModuleContract
{
    public function key(): string;

    public function label(): string;

    /**
     * @return array<int, string>
     */
    public function dependencies(): array;

    public function bootPriority(): int;

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array;
}
