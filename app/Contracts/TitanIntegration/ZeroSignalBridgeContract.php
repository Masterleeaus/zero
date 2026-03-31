<?php

namespace App\Contracts\TitanIntegration;

interface ZeroSignalBridgeContract
{
    public function envelope(int $companyId, ?int $teamId = null, ?int $userId = null): array;
    public function publish(array $signals): array;
    public function timeline(string $processId): array;
}
