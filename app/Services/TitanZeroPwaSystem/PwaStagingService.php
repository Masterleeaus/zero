<?php

namespace App\Services\TitanZeroPwaSystem;

use App\Models\TzPwaDevice;
use App\Models\TzPwaStagedArtifact;
use Illuminate\Support\Facades\Log;

/**
 * PwaStagingService
 *
 * Handles server-side receipt and reconciliation of offline staged artifacts
 * (photos, notes, proofs, attachments) captured by PWA field devices.
 *
 * Architecture:
 * - Artifacts arrive from clients as metadata-only records (no binary upload at this stage)
 * - Server stores artifact metadata in tz_pwa_staged_artifacts
 * - A separate reconciliation pass (manual or scheduled) promotes them into canonical proof/job/note systems
 * - Large file upload is not attempted here — only metadata is stored safely
 */
class PwaStagingService
{
    /**
     * Stage one or more artifacts received from a device.
     *
     * @param  array[]  $artifacts
     * @return array[]  Per-artifact staging results
     */
    public function stageArtifacts(array $artifacts, string $nodeId, int $companyId, int $userId): array
    {
        $results = [];

        foreach ($artifacts as $artifact) {
            $artifact = (array) $artifact;
            $clientRef = $artifact['client_ref'] ?? null;

            // Idempotency: if the client_ref already exists for this node, return existing
            if ($clientRef) {
                $existing = TzPwaStagedArtifact::where('node_id', $nodeId)
                    ->where('client_ref', $clientRef)
                    ->first();

                if ($existing) {
                    $results[] = [
                        'client_ref'    => $clientRef,
                        'status'        => 'duplicate',
                        'artifact_id'   => $existing->id,
                        'artifact_stage' => $existing->artifact_stage,
                    ];
                    continue;
                }
            }

            try {
                $staged = TzPwaStagedArtifact::create([
                    'company_id'         => $companyId,
                    'user_id'            => $userId,
                    'node_id'            => $nodeId,
                    'client_ref'         => $clientRef,
                    'artifact_type'      => $artifact['artifact_type'] ?? 'photo',
                    'artifact_stage'     => 'pending',
                    'job_id'             => $artifact['job_id'] ?? null,
                    'process_id'         => $artifact['process_id'] ?? null,
                    'signal_ref'         => $artifact['signal_ref'] ?? null,
                    'artifact_meta'      => $artifact['meta'] ?? [],
                    'note_body'          => $artifact['note_body'] ?? null,
                    'filename'           => $artifact['filename'] ?? null,
                    'mime_type'          => $artifact['mime_type'] ?? null,
                    'file_size_bytes'    => $artifact['file_size_bytes'] ?? null,
                    'client_captured_at' => isset($artifact['captured_at'])
                        ? \Carbon\Carbon::parse($artifact['captured_at'])
                        : null,
                ]);

                $results[] = [
                    'client_ref'    => $clientRef,
                    'status'        => 'staged',
                    'artifact_id'   => $staged->id,
                    'artifact_stage' => 'pending',
                ];
            } catch (\Throwable $e) {
                Log::warning('[PwaStagingService] Failed to stage artifact', [
                    'node_id'    => $nodeId,
                    'client_ref' => $clientRef,
                    'error'      => $e->getMessage(),
                ]);

                $results[] = [
                    'client_ref' => $clientRef,
                    'status'     => 'error',
                    'reason'     => 'Server staging failed',
                ];
            }
        }

        return $results;
    }

    /**
     * Return a staging status summary for a device.
     */
    public function statusForNode(string $nodeId, int $companyId): array
    {
        $artifacts = TzPwaStagedArtifact::where('node_id', $nodeId)
            ->where('company_id', $companyId)
            ->select('artifact_type', 'artifact_stage', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('artifact_type', 'artifact_stage')
            ->get();

        $pending       = 0;
        $reconciled    = 0;
        $failed        = 0;
        $byType        = [];

        foreach ($artifacts as $row) {
            $byType[$row->artifact_type][$row->artifact_stage] = $row->count;

            if ($row->artifact_stage === 'pending') {
                $pending += $row->count;
            } elseif ($row->artifact_stage === 'reconciled') {
                $reconciled += $row->count;
            } elseif ($row->artifact_stage === 'failed') {
                $failed += $row->count;
            }
        }

        return [
            'node_id'    => $nodeId,
            'pending'    => $pending,
            'reconciled' => $reconciled,
            'failed'     => $failed,
            'by_type'    => $byType,
        ];
    }

    /**
     * Mark an artifact as reconciled into a canonical record.
     */
    public function markReconciled(int $artifactId, int $companyId, int $reconciledToId, string $reconciledToType): bool
    {
        $artifact = TzPwaStagedArtifact::where('id', $artifactId)
            ->where('company_id', $companyId)
            ->first();

        if (! $artifact) {
            return false;
        }

        $artifact->update([
            'artifact_stage'      => 'reconciled',
            'reconciled_to_id'    => $reconciledToId,
            'reconciled_to_type'  => $reconciledToType,
            'reconciled_at'       => now(),
        ]);

        return true;
    }
}
