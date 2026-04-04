<?php

declare(strict_types=1);

namespace App\Services\Work;

use App\Events\Work\ContractExpired;
use App\Events\Work\ContractRenewed;
use App\Models\Work\ContractRenewal;
use App\Models\Work\ServiceAgreement;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ContractRenewalService
{
    /**
     * Renew a contract, creating a new ServiceAgreement linked to the old one.
     *
     * @param array{
     *     new_expiry?: string|null,
     *     billing_amount?: float|null,
     *     billing_cycle?: string|null,
     * } $overrides
     */
    public function renewContract(ServiceAgreement $agreement, array $overrides = []): ServiceAgreement
    {
        $previousExpiry = $agreement->expired_at?->toDateString();
        $newExpiry      = $overrides['new_expiry'] ?? $this->computeNewExpiry($agreement);

        $newAgreement = ServiceAgreement::create(array_merge(
            $agreement->only([
                'company_id',
                'customer_id',
                'site_id',
                'premises_id',
                'deal_id',
                'title',
                'frequency',
                'contract_type',
                'billing_cycle',
                'billing_amount',
                'sla_response_hours',
                'sla_resolution_hours',
                'auto_renews',
                'renewal_notice_days',
                'has_equipment_coverage',
            ]),
            [
                'status'          => 'active',
                'contract_number' => $this->generateContractNumber(),
                'renewed_from_id' => $agreement->id,
                'health_score'    => 100,
                'health_flags'    => null,
                'expired_at'      => $newExpiry ? Carbon::parse($newExpiry) : null,
                'next_run_at'     => $agreement->next_run_at,
            ],
            array_filter($overrides, static fn ($v) => $v !== null)
        ));

        ContractRenewal::create([
            'company_id'      => $agreement->company_id,
            'agreement_id'    => $agreement->id,
            'renewed_to_id'   => $newAgreement->id,
            'renewed_at'      => now(),
            'renewed_by'      => Auth::id(),
            'previous_expiry' => $previousExpiry,
            'new_expiry'      => $newExpiry,
        ]);

        $agreement->update(['status' => 'renewed']);

        ContractRenewed::dispatch($agreement, $newAgreement);

        return $newAgreement;
    }

    /**
     * Return all active agreements due for renewal within $withinDays days.
     */
    public function getDueForRenewal(int $companyId, int $withinDays = 30): Collection
    {
        return ServiceAgreement::where('company_id', $companyId)
            ->where('status', 'active')
            ->where('auto_renews', true)
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', now()->addDays($withinDays))
            ->orderBy('expired_at')
            ->get();
    }

    /**
     * Process all auto-renewable contracts due within the given window.
     *
     * @return array{processed: int, renewed: int, errors: array}
     */
    public function processAutoRenewals(int $companyId, int $withinDays = 0): array
    {
        $due     = $this->getDueForRenewal($companyId, $withinDays);
        $results = ['processed' => 0, 'renewed' => 0, 'errors' => []];

        foreach ($due as $agreement) {
            $results['processed']++;

            try {
                if ($agreement->isExpired()) {
                    ContractExpired::dispatch($agreement);
                }

                $this->renewContract($agreement);
                $results['renewed']++;
            } catch (\Throwable $e) {
                $results['errors'][] = [
                    'agreement_id' => $agreement->id,
                    'error'        => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    protected function computeNewExpiry(ServiceAgreement $agreement): ?string
    {
        if ($agreement->expired_at === null) {
            return null;
        }

        $base = $agreement->expired_at->isFuture()
            ? $agreement->expired_at
            : now();

        return match ($agreement->billing_cycle ?? 'annual') {
            'monthly'   => $base->addMonth()->toDateString(),
            'quarterly' => $base->addMonths(3)->toDateString(),
            default     => $base->addYear()->toDateString(),
        };
    }

    protected function generateContractNumber(): string
    {
        return 'CNT-' . strtoupper(Str::random(8));
    }
}
