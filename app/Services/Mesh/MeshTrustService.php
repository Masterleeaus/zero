<?php

declare(strict_types=1);

namespace App\Services\Mesh;

use App\Events\Mesh\MeshTrustChanged;
use App\Models\Mesh\MeshNode;
use App\Models\Mesh\MeshTrustEvent;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MeshTrustService
{
    /**
     * Append a trust event to the immutable event log.
     */
    public function recordTrustEvent(MeshNode $node, string $eventType, array $payload): MeshTrustEvent
    {
        /** @var MeshTrustEvent $event */
        $event = MeshTrustEvent::create([
            'company_id'  => $node->company_id,
            'node_id'     => $node->node_id,
            'event_type'  => $eventType,
            'payload'     => $payload,
            'occurred_at' => now(),
        ]);

        return $event;
    }

    /**
     * Compute a trust score (0.0–1.0) based on completed jobs vs disputes.
     * Uses DB aggregation to avoid loading all trust events into memory.
     */
    public function computeTrustScore(MeshNode $node): float
    {
        $aggregates = MeshTrustEvent::forNode($node->node_id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN event_type = 'job_completed' THEN 1 ELSE 0 END) as completed")
            ->selectRaw("SUM(CASE WHEN event_type = 'dispute_raised' THEN 1 ELSE 0 END) as disputes")
            ->first();

        $total = (int) ($aggregates?->total ?? 0);

        if ($total === 0) {
            return 0.0;
        }

        $completed = (int) ($aggregates?->completed ?? 0);
        $disputes  = (int) ($aggregates?->disputes ?? 0);

        // Simple weighted scoring: +1 completed, -2 disputes
        $score = ($completed - ($disputes * 2)) / max($total, 1);

        return (float) max(0.0, min(1.0, $score));
    }

    /**
     * Upgrade the trust level of a node (one level at a time).
     */
    public function upgradeTrust(MeshNode $node, User $authorisedBy): void
    {
        $levels  = MeshNode::TRUST_LEVELS;
        $current = array_search($node->trust_level, $levels, true);

        if ($current === false || $current >= count($levels) - 1) {
            Log::info('MeshTrustService: node already at maximum trust level', [
                'node_id'     => $node->node_id,
                'trust_level' => $node->trust_level,
            ]);
            return;
        }

        $newLevel = $levels[$current + 1];
        $node->update(['trust_level' => $newLevel]);

        $this->recordTrustEvent($node, 'trust_upgraded', [
            'from'           => $levels[$current],
            'to'             => $newLevel,
            'authorised_by'  => $authorisedBy->id,
        ]);

        MeshTrustChanged::dispatch($node, $levels[$current], $newLevel);
    }

    /**
     * Suspend a node — mark inactive and record the event.
     */
    public function suspendNode(MeshNode $node, string $reason, User $authorisedBy): void
    {
        $previousLevel = $node->trust_level;
        $node->update(['is_active' => false]);

        $this->recordTrustEvent($node, 'node_suspended', [
            'reason'         => $reason,
            'authorised_by'  => $authorisedBy->id,
            'previous_level' => $previousLevel,
        ]);

        MeshTrustChanged::dispatch($node, $previousLevel, 'suspended');
    }
}
