<?php

namespace App\Services\Omni\Contracts;

interface LegacyChannelBridgeContract
{
    public function normalize(array $payload): array;

    public function mirrorToOmni(array $payload): array;
}
