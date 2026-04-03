<?php

declare(strict_types=1);

namespace App\Services\Work;

use App\Events\Work\ContractEntitlementExhausted;
use App\Models\Work\ContractEntitlement;
use App\Models\Work\ServiceAgreement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ContractEntitlementService
{
    /**
     * Check whether the given service type is entitled under the agreement.
     *
     * Returns true if the entitlement exists and has capacity, or is unlimited.
     * Returns true if no entitlement record exists for the service type (open access).
     */
    public function checkEntitlement(ServiceAgreement $agreement, string $serviceType): bool
    {
        $entitlement = $this->resolveEntitlement($agreement, $serviceType);

        if ($entitlement === null) {
            return true;
        }

        if ($entitlement->is_unlimited) {
            return true;
        }

        if ($entitlement->isVisitExhausted()) {
            return false;
        }

        return true;
    }

    /**
     * Consume one visit from the entitlement (within a DB transaction).
     *
     * Fires ContractEntitlementExhausted if the entitlement is now exhausted.
     *
     * @throws \RuntimeException if entitlement is exhausted before consumption.
     */
    public function consumeEntitlement(ServiceAgreement $agreement, string $serviceType, float $hoursConsumed = 0.0): void
    {
        DB::transaction(function () use ($agreement, $serviceType, $hoursConsumed) {
            $entitlement = ContractEntitlement::where('agreement_id', $agreement->id)
                ->where('service_type', $serviceType)
                ->lockForUpdate()
                ->first();

            if ($entitlement === null) {
                return;
            }

            if (! $entitlement->is_unlimited) {
                if ($entitlement->max_visits !== null && $entitlement->isVisitExhausted()) {
                    throw new \RuntimeException(
                        "Entitlement exhausted for service type [{$serviceType}] on agreement [{$agreement->id}]."
                    );
                }

                if ($entitlement->max_visits !== null) {
                    $entitlement->increment('visits_used');
                }

                if ($entitlement->max_hours !== null && $hoursConsumed > 0) {
                    $entitlement->increment('hours_used', $hoursConsumed);
                }

                $entitlement->refresh();

                if ($entitlement->isVisitExhausted() || $entitlement->isHoursExhausted()) {
                    ContractEntitlementExhausted::dispatch($agreement, $entitlement);
                }
            }
        });
    }

    /**
     * Return remaining entitlement details for the given service type.
     *
     * @return array{
     *     service_type: string,
     *     is_unlimited: bool,
     *     remaining_visits: int|null,
     *     remaining_hours: float|null,
     *     visits_used: int,
     *     hours_used: float,
     *     resets_on: string|null
     * }
     */
    public function getRemainingEntitlement(ServiceAgreement $agreement, string $serviceType): array
    {
        $entitlement = $this->resolveEntitlement($agreement, $serviceType);

        if ($entitlement === null) {
            return [
                'service_type'     => $serviceType,
                'is_unlimited'     => true,
                'remaining_visits' => null,
                'remaining_hours'  => null,
                'visits_used'      => 0,
                'hours_used'       => 0.0,
                'resets_on'        => null,
            ];
        }

        return [
            'service_type'     => $serviceType,
            'is_unlimited'     => $entitlement->is_unlimited,
            'remaining_visits' => $entitlement->remainingVisits(),
            'remaining_hours'  => $entitlement->remainingHours(),
            'visits_used'      => $entitlement->visits_used,
            'hours_used'       => (float) $entitlement->hours_used,
            'resets_on'        => $entitlement->resets_on?->toDateString(),
        ];
    }

    /**
     * Reset all period-based entitlements that are due for reset.
     */
    public function resetPeriodEntitlements(ServiceAgreement $agreement): int
    {
        $count = 0;

        $agreement->entitlements()
            ->where('period_type', '!=', 'contract')
            ->whereNotNull('resets_on')
            ->where('resets_on', '<=', now()->toDateString())
            ->each(function (ContractEntitlement $entitlement) use (&$count) {
                $entitlement->update([
                    'visits_used' => 0,
                    'hours_used'  => 0,
                    'resets_on'   => $this->computeNextResetDate($entitlement),
                ]);
                $count++;
            });

        return $count;
    }

    protected function resolveEntitlement(ServiceAgreement $agreement, string $serviceType): ?ContractEntitlement
    {
        return ContractEntitlement::where('agreement_id', $agreement->id)
            ->where('service_type', $serviceType)
            ->first();
    }

    protected function computeNextResetDate(ContractEntitlement $entitlement): ?string
    {
        if ($entitlement->resets_on === null) {
            return null;
        }

        $current = Carbon::parse($entitlement->resets_on);

        return match ($entitlement->period_type) {
            'monthly'   => $current->addMonth()->toDateString(),
            'quarterly' => $current->addMonths(3)->toDateString(),
            'annual'    => $current->addYear()->toDateString(),
            default     => null,
        };
    }
}
