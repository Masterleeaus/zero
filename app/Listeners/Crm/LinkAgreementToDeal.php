<?php

declare(strict_types=1);

namespace App\Listeners\Crm;

use App\Events\Crm\ServiceJobCreatedFromDeal;
use App\Models\Work\ServiceAgreement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * When a service job is created from a CRM deal, link any active agreement
 * for that customer/deal combination back to the deal.
 */
class LinkAgreementToDeal implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function handle(ServiceJobCreatedFromDeal $event): void
    {
        $job  = $event->job;
        $deal = $event->deal;

        if (! $job->agreement_id) {
            return;
        }

        try {
            $agreement = ServiceAgreement::find($job->agreement_id);

            if ($agreement && $agreement->deal_id === null) {
                $agreement->update(['deal_id' => $deal->id]);
            }
        } catch (\Throwable $th) {
            Log::error('LinkAgreementToDeal: ' . $th->getMessage(), [
                'job_id'  => $job->id,
                'deal_id' => $deal->id,
            ]);
        }
    }
}
