<?php

declare(strict_types=1);

namespace App\Services\Work;

use App\Events\Work\AgreementEquipmentCoverageCreated;
use App\Events\Work\AgreementEquipmentCoverageExtended;
use App\Events\Work\RecurringEquipmentServiceCreated;
use App\Models\Equipment\Equipment;
use App\Models\Equipment\InstalledEquipment;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServicePlan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * EquipmentCoverageService
 *
 * Manages the lifecycle of agreement-sold equipment coverage.
 *
 * Implements Odoo behaviours from:
 *   - fieldservice_sale_agreement_equipment_stock
 *
 * Responsibilities:
 *   A) Activate coverage for an equipment instance when sold through an agreement
 *   B) Extend coverage when the same agreement is renewed / upgraded
 *   C) Query covered equipment for a given agreement
 *   D) Build the coverage timeline for a given agreement
 *   E) Create recurring service plans for covered equipment
 */
class EquipmentCoverageService
{
    // ── A: Activate coverage ─────────────────────────────────────────────────

    /**
     * Activate agreement coverage for a specific InstalledEquipment instance.
     *
     * Mirrors Odoo fieldservice_sale_agreement_equipment_stock:
     *   equipment sold through agreement → equipment coverage record updated.
     *
     * @param  array<string, mixed>  $options  {
     *     coverage_start_date?: string,
     *     coverage_end_date?: string,
     *     sale_quote_id?: int
     * }
     */
    public function activateCoverageForEquipment(
        ServiceAgreement $agreement,
        InstalledEquipment $installedEquipment,
        array $options = [],
    ): InstalledEquipment {
        return DB::transaction(function () use ($agreement, $installedEquipment, $options) {
            $alreadyCovered = $installedEquipment->agreement_id === $agreement->id
                && $installedEquipment->coverage_activated_at !== null;

            $installedEquipment->fill([
                'agreement_id'          => $agreement->id,
                'sale_quote_id'         => $options['sale_quote_id'] ?? $agreement->originating_quote_id,
                'coverage_start_date'   => $options['coverage_start_date'] ?? now()->toDateString(),
                'coverage_end_date'     => $options['coverage_end_date'] ?? null,
                'coverage_activated_at' => $installedEquipment->coverage_activated_at ?? now(),
            ]);
            $installedEquipment->save();

            // Ensure the agreement is flagged as having equipment coverage
            if (! $agreement->has_equipment_coverage) {
                $agreement->has_equipment_coverage = true;
                $agreement->save();
            }

            if ($alreadyCovered) {
                AgreementEquipmentCoverageExtended::dispatch($agreement, $installedEquipment);
            } else {
                AgreementEquipmentCoverageCreated::dispatch($agreement, $installedEquipment);
            }

            return $installedEquipment->fresh();
        });
    }

    /**
     * Activate coverage from a catalogue Equipment record.
     *
     * Locates the active InstalledEquipment for that equipment and
     * activates agreement coverage on it. Creates an InstalledEquipment
     * record if none exists.
     *
     * @param  array<string, mixed>  $options
     */
    public function activateCoverageFromEquipment(
        ServiceAgreement $agreement,
        Equipment $equipment,
        array $options = [],
    ): InstalledEquipment {
        $installed = $equipment->currentInstallation();

        if (! $installed) {
            // Create a minimal installation record so coverage can be attached
            $installed = InstalledEquipment::create([
                'company_id'   => $agreement->company_id,
                'equipment_id' => $equipment->id,
                'customer_id'  => $agreement->customer_id,
                'premises_id'  => $agreement->premises_id,
                'status'       => 'active',
                'installed_at' => $options['installed_at'] ?? now()->toDateString(),
                'created_by'   => $agreement->created_by ?? null,
            ]);
        }

        return $this->activateCoverageForEquipment($agreement, $installed, $options);
    }

    // ── B: Extend coverage ───────────────────────────────────────────────────

    /**
     * Extend existing agreement coverage for an installed equipment unit.
     *
     * Used when a renewal sale is processed against an existing agreement
     * that already covers the same equipment.
     *
     * @param  array<string, mixed>  $options  { coverage_end_date?, sale_quote_id? }
     */
    public function extendCoverageForEquipment(
        ServiceAgreement $agreement,
        InstalledEquipment $installedEquipment,
        array $options = [],
    ): InstalledEquipment {
        return DB::transaction(function () use ($agreement, $installedEquipment, $options) {
            $changes = [];

            if (isset($options['coverage_end_date'])) {
                $changes['coverage_end_date'] = $options['coverage_end_date'];
            }

            if (isset($options['sale_quote_id'])) {
                $changes['sale_quote_id'] = $options['sale_quote_id'];
            }

            if (! empty($changes)) {
                $installedEquipment->fill($changes)->save();
            }

            AgreementEquipmentCoverageExtended::dispatch($agreement, $installedEquipment);

            return $installedEquipment->fresh();
        });
    }

    // ── C: Query covered equipment ───────────────────────────────────────────

    /**
     * All InstalledEquipment records currently covered by a given agreement.
     *
     * @return Collection<int, InstalledEquipment>
     */
    public function coveredEquipmentForAgreement(ServiceAgreement $agreement): Collection
    {
        return InstalledEquipment::query()
            ->where('agreement_id', $agreement->id)
            ->where('company_id', $agreement->company_id)
            ->where('status', 'active')
            ->with(['equipment', 'site', 'premises'])
            ->get();
    }

    /**
     * InstalledEquipment records covered by the agreement AND with active coverage dates.
     *
     * @return Collection<int, InstalledEquipment>
     */
    public function activelyCoveredEquipment(ServiceAgreement $agreement): Collection
    {
        return InstalledEquipment::query()
            ->where('agreement_id', $agreement->id)
            ->where('company_id', $agreement->company_id)
            ->where('status', 'active')
            ->where(static function ($q) {
                $q->whereNull('coverage_end_date')
                    ->orWhere('coverage_end_date', '>=', now()->toDateString());
            })
            ->whereNotNull('coverage_activated_at')
            ->get();
    }

    // ── D: Coverage timeline ─────────────────────────────────────────────────

    /**
     * Build a structured coverage timeline for a given agreement.
     *
     * Returns per-equipment coverage state alongside service plan visit counts.
     *
     * @return array{
     *     agreement_id: int,
     *     has_equipment_coverage: bool,
     *     covered_count: int,
     *     equipment: array<int, array{
     *         installed_equipment_id: int,
     *         equipment_name: string|null,
     *         coverage_start: string|null,
     *         coverage_end: string|null,
     *         coverage_active: bool,
     *         total_visits: int,
     *         completed_visits: int
     *     }>
     * }
     */
    public function coverageTimeline(ServiceAgreement $agreement): array
    {
        $items = $this->coveredEquipmentForAgreement($agreement);

        $timeline = [];
        foreach ($items as $ie) {
            $visitQuery = \App\Models\Work\ServicePlanVisit::query()
                ->where('installed_equipment_id', $ie->id)
                ->where('company_id', $agreement->company_id);

            $timeline[] = [
                'installed_equipment_id' => $ie->id,
                'equipment_name'         => $ie->equipment?->name,
                'coverage_start'         => $ie->coverage_start_date?->toDateString(),
                'coverage_end'           => $ie->coverage_end_date?->toDateString(),
                'coverage_active'        => $ie->coverage_end_date === null
                    || $ie->coverage_end_date->isFuture(),
                'total_visits'           => (clone $visitQuery)->count(),
                'completed_visits'       => (clone $visitQuery)->where('status', 'completed')->count(),
            ];
        }

        return [
            'agreement_id'           => $agreement->id,
            'has_equipment_coverage' => (bool) $agreement->has_equipment_coverage,
            'covered_count'          => count($timeline),
            'equipment'              => $timeline,
        ];
    }

    // ── E: Create recurring service plan for covered equipment ───────────────

    /**
     * Create a recurring ServicePlan for a specific covered InstalledEquipment unit.
     *
     * Fires RecurringEquipmentServiceCreated.
     *
     * @param  array<string, mixed>  $planAttributes
     */
    public function createEquipmentRecurringPlan(
        ServiceAgreement $agreement,
        InstalledEquipment $installedEquipment,
        array $planAttributes = [],
    ): ServicePlan {
        $plan = DB::transaction(function () use ($agreement, $installedEquipment, $planAttributes) {
            $defaults = [
                'company_id'          => $agreement->company_id,
                'created_by'          => $agreement->created_by ?? null,
                'customer_id'         => $agreement->customer_id,
                'premises_id'         => $installedEquipment->premises_id ?? $agreement->premises_id,
                'agreement_id'        => $agreement->id,
                'origin_quote_id'     => $agreement->originating_quote_id,
                'name'                => $installedEquipment->equipment?->name
                    ? 'Maintenance: ' . $installedEquipment->equipment->name
                    : 'Equipment Maintenance Plan',
                'recurrence_type'     => 'maintenance',
                'auto_generate_visits' => true,
                'equipment_scope'     => [$installedEquipment->id],
                'status'              => 'active',
                'is_active'           => true,
                'frequency'           => 'monthly',
            ];

            $plan = ServicePlan::create(array_merge($defaults, $planAttributes));

            // Increment the cached plan count on the agreement
            $agreement->increment('recurring_plan_count');

            return $plan;
        });

        RecurringEquipmentServiceCreated::dispatch($plan, $installedEquipment);

        return $plan;
    }

    // ── F: Bulk activation from quote ────────────────────────────────────────

    /**
     * Activate coverage for all Equipment records linked to a quote's sale lines.
     *
     * Iterates quote items that have 'field_service_tracking' set to 'sale' or 'line'
     * and activates coverage for any equipment linked to those items.
     *
     * @param  \App\Models\Money\Quote  $quote
     * @return Collection<int, InstalledEquipment>
     */
    public function activateCoverageFromQuote(
        \App\Models\Money\Quote $quote,
        ServiceAgreement $agreement,
    ): Collection {
        $activated = collect();

        /** @var \App\Models\Money\QuoteItem $item */
        foreach ($quote->items ?? [] as $item) {
            if (! $item->field_service_tracking || $item->field_service_tracking === 'no') {
                continue;
            }

            // If the quote item carries an equipment_id, activate coverage for it
            if (! empty($item->equipment_id)) {
                $equipment = Equipment::find($item->equipment_id);
                if ($equipment) {
                    $installed = $this->activateCoverageFromEquipment(
                        $agreement,
                        $equipment,
                        ['sale_quote_id' => $quote->id]
                    );
                    $activated->push($installed);
                }
            }
        }

        return $activated;
    }
}
