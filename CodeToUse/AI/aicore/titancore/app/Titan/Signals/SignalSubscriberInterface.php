<?php

namespace App\Titan\Signals;

interface SignalSubscriberInterface
{
    public function name(): string;

    public function handle(array $signal): array;
}
