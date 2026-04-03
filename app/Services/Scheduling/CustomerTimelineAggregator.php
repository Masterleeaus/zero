<?php

declare(strict_types=1);

namespace App\Services\Scheduling;

use App\Models\Crm\Customer;
use App\Models\Facility\SiteAsset;
use App\Models\Inspection\InspectionInstance;
use App\Models\Premises\Premises;
use App\Models\Work\ChecklistRun;
use App\Models\Work\ServiceJob;
use App\Models\Work\ServicePlanVisit;
use Illuminate\Support\Collection;

/**
 * CustomerTimelineAggregator — merges all service-related events for a customer
 * into a single, chronologically ordered timeline.
 *
 * Aggregates:
 *   - ServiceJob
 *   - ServicePlanVisit
 *   - InspectionInstance
 *   - ChecklistRun
 *   - Hazards (detected / resolved)
 *   - Equipment maintenance events
 *
 * Returns arrays of timeline entries ordered by event date descending.
 */
class CustomerTimelineAggregator
{
    /**
     * Build the full chronological timeline for a customer.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function forCustomer(Customer $customer): Collection
    {
        $premisesIds = $customer->premises()->pluck('id');

        $entries = collect();

        // Service jobs
        $entries = $entries->merge(
            ServiceJob::query()
                ->where('customer_id', $customer->id)
                ->whereNotNull('scheduled_date_start')
                ->get()
                ->map(fn (ServiceJob $j) => $this->jobEntry($j))
        );

        // Service plan visits
        $entries = $entries->merge(
            ServicePlanVisit::query()
                ->whereHas('plan', fn ($q) => $q->where('customer_id', $customer->id)
                    ->orWhereIn('premises_id', $premisesIds))
                ->get()
                ->map(fn (ServicePlanVisit $v) => $this->visitEntry($v))
        );

        // Inspections (scoped to premises)
        $entries = $entries->merge(
            InspectionInstance::query()
                ->where(function ($q) use ($premisesIds) {
                    $q->where('scope_type', Premises::class)
                      ->whereIn('scope_id', $premisesIds);
                })
                ->get()
                ->map(fn (InspectionInstance $i) => $this->inspectionEntry($i))
        );

        // Hazards
        $entries = $entries->merge(
            \App\Models\Premises\Hazard::query()
                ->whereIn('premises_id', $premisesIds)
                ->get()
                ->map(fn (\App\Models\Premises\Hazard $h) => $this->hazardEntry($h))
        );

        return $entries
            ->sortByDesc('event_date')
            ->values();
    }

    /**
     * Build the timeline for a premises.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function forPremises(Premises $premises): Collection
    {
        $entries = collect();

        $entries = $entries->merge(
            ServiceJob::query()
                ->where('premises_id', $premises->id)
                ->whereNotNull('scheduled_date_start')
                ->get()
                ->map(fn (ServiceJob $j) => $this->jobEntry($j))
        );

        $entries = $entries->merge(
            ServicePlanVisit::query()
                ->whereHas('plan', fn ($q) => $q->where('premises_id', $premises->id))
                ->get()
                ->map(fn (ServicePlanVisit $v) => $this->visitEntry($v))
        );

        $entries = $entries->merge(
            InspectionInstance::query()
                ->where('scope_type', Premises::class)
                ->where('scope_id', $premises->id)
                ->get()
                ->map(fn (InspectionInstance $i) => $this->inspectionEntry($i))
        );

        $entries = $entries->merge(
            \App\Models\Premises\Hazard::query()
                ->where('premises_id', $premises->id)
                ->get()
                ->map(fn (\App\Models\Premises\Hazard $h) => $this->hazardEntry($h))
        );

        return $entries
            ->sortByDesc('event_date')
            ->values();
    }

    /**
     * Build the service history timeline for a site asset.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function forAsset(SiteAsset $asset): Collection
    {
        $entries = collect();

        $entries = $entries->merge(
            $asset->serviceEvents()
                ->get()
                ->map(fn (\App\Models\Facility\AssetServiceEvent $e) => [
                    'type'        => 'asset_service_event',
                    'entity_type' => \App\Models\Facility\AssetServiceEvent::class,
                    'entity_id'   => $e->id,
                    'label'       => $e->event_type ?? 'Service event',
                    'event_date'  => $e->event_date?->toIso8601String(),
                    'notes'       => $e->notes,
                ])
        );

        $entries = $entries->merge(
            InspectionInstance::query()
                ->where('scope_type', SiteAsset::class)
                ->where('scope_id', $asset->id)
                ->get()
                ->map(fn (InspectionInstance $i) => $this->inspectionEntry($i))
        );

        return $entries
            ->sortByDesc('event_date')
            ->values();
    }

    // ── Private entry builders ────────────────────────────────────────────────

    /**
     * @return array<string, mixed>
     */
    private function jobEntry(ServiceJob $job): array
    {
        return [
            'type'        => 'service_job',
            'entity_type' => ServiceJob::class,
            'entity_id'   => $job->id,
            'label'       => $job->getSchedulableTitle(),
            'event_date'  => $job->getScheduledStart(),
            'status'      => $job->getSchedulableStatus(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function visitEntry(ServicePlanVisit $visit): array
    {
        return [
            'type'        => 'service_plan_visit',
            'entity_type' => ServicePlanVisit::class,
            'entity_id'   => $visit->id,
            'label'       => $visit->getSchedulableTitle(),
            'event_date'  => $visit->getScheduledStart(),
            'status'      => $visit->getSchedulableStatus(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function inspectionEntry(InspectionInstance $inspection): array
    {
        return [
            'type'        => 'inspection',
            'entity_type' => InspectionInstance::class,
            'entity_id'   => $inspection->id,
            'label'       => $inspection->getSchedulableTitle(),
            'event_date'  => $inspection->getScheduledStart(),
            'status'      => $inspection->getSchedulableStatus(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function hazardEntry(\App\Models\Premises\Hazard $hazard): array
    {
        $date = $hazard->resolved_at ?? $hazard->identified_at;

        return [
            'type'        => 'hazard',
            'entity_type' => \App\Models\Premises\Hazard::class,
            'entity_id'   => $hazard->id,
            'label'       => $hazard->title ?? 'Hazard',
            'event_date'  => $date?->toIso8601String(),
            'status'      => $hazard->status,
            'severity'    => $hazard->severity,
        ];
    }
}
