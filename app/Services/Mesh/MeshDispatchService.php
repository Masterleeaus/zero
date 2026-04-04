<?php

declare(strict_types=1);

namespace App\Services\Mesh;

use App\Events\Mesh\MeshDispatchAccepted;
use App\Events\Mesh\MeshDispatchCompleted;
use App\Events\Mesh\MeshDispatchRequested;
use App\Models\Mesh\MeshDispatchRequest;
use App\Models\Mesh\MeshNode;
use App\Models\Work\ServiceJob;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MeshDispatchService
{
    /**
     * Create a new mesh dispatch request for an unfulfillable job.
     */
    public function requestFulfillment(ServiceJob $job, array $requiredCapabilities): MeshDispatchRequest
    {
        /** @var MeshDispatchRequest $request */
        $request = MeshDispatchRequest::create([
            'requesting_company_id' => $job->company_id,
            'original_job_id'       => $job->id,
            'required_capabilities' => $requiredCapabilities,
            'location'              => $this->extractJobLocation($job),
            'urgency'               => 'normal',
            'status'                => MeshDispatchRequest::STATUS_OPEN,
            'mesh_job_reference'    => Str::upper(Str::random(12)),
        ]);

        MeshDispatchRequested::dispatch($request, $job);

        return $request;
    }

    /**
     * Find active, trusted peer nodes capable of fulfilling the request.
     */
    public function findFulfillingNodes(MeshDispatchRequest $request): Collection
    {
        return MeshNode::withoutGlobalScope('company')
            ->where('company_id', '!=', $request->requesting_company_id)
            ->active()
            ->withMinTrustLevel(MeshNode::TRUST_STANDARD)
            ->get();
    }

    /**
     * Offer the dispatch request to a specific peer node.
     */
    public function offerToNode(MeshDispatchRequest $request, MeshNode $node): void
    {
        $request->update([
            'fulfilling_company_id' => $node->company_id,
            'status'                => MeshDispatchRequest::STATUS_OFFERED,
            'offered_at'            => now(),
        ]);
    }

    /**
     * Accept an offered dispatch request.
     */
    public function acceptOffer(MeshDispatchRequest $request): void
    {
        $request->update([
            'status'      => MeshDispatchRequest::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);

        MeshDispatchAccepted::dispatch($request);
    }

    /**
     * Mark the request as completed and attach evidence hash.
     *
     * The evidence hash is produced from a canonically serialised representation
     * (recursively sorted keys) to ensure consistent hashes across different peer
     * implementations regardless of JSON key order.
     */
    public function completeRequest(MeshDispatchRequest $request, array $evidenceData): void
    {
        $evidenceHash = hash('sha256', $this->canonicalise($evidenceData));

        $request->update([
            'status'        => MeshDispatchRequest::STATUS_COMPLETED,
            'completed_at'  => now(),
            'evidence_hash' => $evidenceHash,
        ]);

        MeshDispatchCompleted::dispatch($request, $evidenceData);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function extractJobLocation(ServiceJob $job): ?array
    {
        if (isset($job->latitude, $job->longitude)) {
            return ['lat' => $job->latitude, 'lng' => $job->longitude];
        }

        return null;
    }

    /**
     * Produce a deterministic canonical JSON string for hashing.
     * Recursively sorts associative array keys; preserves list ordering.
     */
    private function canonicalise(array $data): string
    {
        return json_encode(
            $this->canonicaliseValue($data),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
    }

    /** @return mixed */
    private function canonicaliseValue(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $v) {
            $value[$key] = $this->canonicaliseValue($v);
        }

        if (! array_is_list($value)) {
            ksort($value);
        }

        return $value;
    }
}
