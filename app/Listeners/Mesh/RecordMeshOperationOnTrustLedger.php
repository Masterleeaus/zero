<?php

declare(strict_types=1);

namespace App\Listeners\Mesh;

use App\Events\Mesh\MeshDispatchCompleted;
use App\Events\Mesh\MeshNodeHandshaked;
use Illuminate\Support\Facades\Log;

/**
 * Record every mesh operation on the Trust Ledger (Module 03 integration).
 *
 * The listener is intentionally tolerant: if TrustLedgerService is not yet
 * available (Modules 01–06 gate not passed), it logs and continues silently
 * rather than breaking the mesh workflow.
 */
class RecordMeshOperationOnTrustLedger
{
    public function handleHandshake(MeshNodeHandshaked $event): void
    {
        $this->record('mesh_node_handshaked', [
            'node_id'     => $event->node->node_id,
            'company_id'  => $event->node->company_id,
            'trust_level' => $event->node->trust_level,
        ]);
    }

    public function handleCompleted(MeshDispatchCompleted $event): void
    {
        $this->record('mesh_job_completed', [
            'mesh_dispatch_request_id' => $event->request->id,
            'requesting_company_id'    => $event->request->requesting_company_id,
            'fulfilling_company_id'    => $event->request->fulfilling_company_id,
            'evidence_hash'            => $event->request->evidence_hash,
        ]);
    }

    private function record(string $entryType, array $payload): void
    {
        if (! class_exists(\App\Services\Trust\TrustLedgerService::class)) {
            Log::debug('RecordMeshOperationOnTrustLedger: TrustLedgerService not available, skipping.', [
                'entry_type' => $entryType,
            ]);
            return;
        }

        try {
            /** @var \App\Services\Trust\TrustLedgerService $ledger */
            $ledger = app(\App\Services\Trust\TrustLedgerService::class);
            $ledger->record($entryType, $payload);
        } catch (\Throwable $e) {
            Log::warning('RecordMeshOperationOnTrustLedger: failed to record entry.', [
                'entry_type' => $entryType,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
