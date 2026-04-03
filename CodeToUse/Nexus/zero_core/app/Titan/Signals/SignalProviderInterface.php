<?php

namespace App\Titan\Signals;

interface SignalProviderInterface
{
    /** @return Signal[] */
    public function getSignals(int $companyId, ?int $teamId = null, ?int $userId = null): array;

    public function sourceEngine(): string;
}
