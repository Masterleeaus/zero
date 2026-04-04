<?php

declare(strict_types=1);

namespace App\Listeners\Work;

use App\Events\Work\ContractExpired;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Notify relevant parties when a contract has expired.
 */
class NotifyOnContractExpiry implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function handle(ContractExpired $event): void
    {
        $agreement = $event->agreement;

        try {
            Log::info('ContractExpired notification', [
                'agreement_id'    => $agreement->id,
                'company_id'      => $agreement->company_id,
                'contract_number' => $agreement->contract_number,
                'expired_at'      => $agreement->expired_at?->toDateTimeString(),
            ]);

            // Notification dispatch can be extended here (e.g. Mail, Notification).
        } catch (\Throwable $th) {
            Log::error('NotifyOnContractExpiry: ' . $th->getMessage(), [
                'agreement_id' => $agreement->id,
            ]);
        }
    }
}
