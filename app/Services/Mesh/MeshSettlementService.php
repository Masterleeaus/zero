<?php

declare(strict_types=1);

namespace App\Services\Mesh;

use App\Events\Mesh\MeshSettlementDue;
use App\Models\Mesh\MeshDispatchRequest;
use App\Models\Mesh\MeshSettlement;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MeshSettlementService
{
    /**
     * Calculate and persist a settlement record for a completed dispatch request.
     *
     * @throws \InvalidArgumentException if the dispatch request has no fulfilling company.
     */
    public function calculateSettlement(MeshDispatchRequest $request): MeshSettlement
    {
        if ($request->fulfilling_company_id === null) {
            throw new \InvalidArgumentException(
                "Cannot create settlement for dispatch request [{$request->id}]: fulfilling_company_id is not set. " .
                'A request must be accepted by a fulfilling node before settlement can be calculated.'
            );
        }

        $amount           = $this->deriveAmount($request);
        $commissionAmount = round($amount * (float) $request->commission_rate, 2);
        $netAmount        = round($amount - $commissionAmount, 2);

        /** @var MeshSettlement $settlement */
        $settlement = MeshSettlement::updateOrCreate(
            ['mesh_dispatch_request_id' => $request->id],
            [
                'requesting_company_id' => $request->requesting_company_id,
                'fulfilling_company_id' => $request->fulfilling_company_id,
                'amount'                => $amount,
                'commission_amount'     => $commissionAmount,
                'net_amount'            => $netAmount,
                'currency'              => 'AUD',
                'status'                => MeshSettlement::STATUS_PENDING,
            ]
        );

        MeshSettlementDue::dispatch($settlement);

        return $settlement;
    }

    /**
     * Mark a settlement as invoiced and assign a reference number.
     */
    public function invoiceSettlement(MeshSettlement $settlement): void
    {
        $settlement->update([
            'status'            => MeshSettlement::STATUS_INVOICED,
            'invoice_reference' => 'MESH-' . Str::upper(Str::random(8)),
        ]);
    }

    /**
     * Return all pending settlements for a company (as requesting or fulfilling party).
     */
    public function getPendingSettlements(int $companyId): Collection
    {
        return MeshSettlement::pending()
            ->forCompany($companyId)
            ->with('dispatchRequest')
            ->get();
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * Derive the settlement amount.
     * In a full implementation this would pull from job billing.
     * For MVP we use a fixed placeholder; override via config.
     */
    private function deriveAmount(MeshDispatchRequest $request): float
    {
        return (float) config('mesh.default_settlement_amount', 250.00);
    }
}
