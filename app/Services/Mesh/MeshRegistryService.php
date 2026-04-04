<?php

declare(strict_types=1);

namespace App\Services\Mesh;

use App\Events\Mesh\MeshNodeHandshaked;
use App\Models\Mesh\MeshCapabilityExport;
use App\Models\Mesh\MeshNode;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class MeshRegistryService
{
    private const FSM_PATH    = 'fsm_module_status.json';
    private const GATE_MODULES = [
        'titan_dispatch',
        'capability_registry',
        'trust_work_ledger',
        'titan_contracts',
        'edge_sync',
        'execution_time_graph',
    ];

    /**
     * Register a new peer node for the given company.
     *
     * @throws RuntimeException if the activation gate has not been satisfied.
     */
    public function registerNode(int $companyId, array $nodeData): MeshNode
    {
        $this->assertActivationGate();

        /** @var MeshNode $node */
        $node = MeshNode::withoutGlobalScope('company')->updateOrCreate(
            [
                'company_id' => $companyId,
                'node_id'    => $nodeData['node_id'] ?? Str::uuid()->toString(),
            ],
            array_merge($nodeData, ['company_id' => $companyId]),
        );

        return $node;
    }

    /**
     * Perform a handshake with a peer node — update last_handshake_at and fire event.
     */
    public function performHandshake(MeshNode $node): bool
    {
        $node->last_handshake_at = now();
        $node->save();

        MeshNodeHandshaked::dispatch($node);

        return true;
    }

    /**
     * Rebuild and return the capability export summary for a company.
     */
    public function exportCapabilities(int $companyId): array
    {
        return MeshCapabilityExport::forCompany($companyId)
            ->exported()
            ->get()
            ->map(fn (MeshCapabilityExport $e) => [
                'type'             => $e->capability_type,
                'value'            => $e->capability_value,
                'available_count'  => $e->available_count,
                'geographic_scope' => $e->geographic_scope,
            ])
            ->values()
            ->all();
    }

    /**
     * Return all active peer nodes at or above the given trust level.
     */
    public function getTrustedPeers(int $companyId, string $minTrustLevel = 'standard'): Collection
    {
        return MeshNode::forCompany($companyId)
            ->active()
            ->withMinTrustLevel($minTrustLevel)
            ->get();
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * Verify that all prerequisite modules (01–06) are in `installed` state.
     *
     * @throws RuntimeException
     */
    private function assertActivationGate(): void
    {
        $fsmPath = base_path(self::FSM_PATH);

        if (! file_exists($fsmPath)) {
            throw new RuntimeException('TitanMesh activation gate: fsm_module_status.json not found.');
        }

        $fsm     = json_decode(file_get_contents($fsmPath), true) ?? [];
        $modules = $fsm['modules'] ?? [];

        foreach (self::GATE_MODULES as $key) {
            $status = $modules[$key]['status'] ?? 'not_found';

            if ($status !== 'installed') {
                throw new RuntimeException(
                    "TitanMesh activation gate: module [{$key}] must be in `installed` state (current: {$status}). " .
                    'Complete Modules 01–06 before activating TitanMesh.'
                );
            }
        }
    }
}
