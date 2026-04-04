<?php

declare(strict_types=1);

namespace App\Services\Mesh;

use App\Models\Mesh\MeshNode;

class MeshSignatureService
{
    private const ALGORITHM = 'sha256';

    /**
     * Sign a payload using the company's own node key (stored in config or resolved at runtime).
     *
     * @return string Base64-encoded HMAC-SHA256 signature
     */
    public function signPayload(array $payload, int $companyId): string
    {
        $secret = $this->resolveOwnSecret($companyId);

        return base64_encode(hash_hmac(
            self::ALGORITHM,
            $this->canonicalise($payload),
            $secret,
            true,
        ));
    }

    /**
     * Verify a payload was signed by the given peer node.
     *
     * @param  array     $payload   The received payload (without the signature field).
     * @param  string    $signature The received signature (base64-encoded).
     * @param  MeshNode  $fromNode  The peer node that claims to have sent this payload.
     */
    public function verifyPayload(array $payload, string $signature, MeshNode $fromNode): bool
    {
        $expected = base64_encode(hash_hmac(
            self::ALGORITHM,
            $this->canonicalise($payload),
            $fromNode->public_key,
            true,
        ));

        return hash_equals($expected, $signature);
    }

    /**
     * Wrap a payload in a signed mesh envelope.
     *
     * @return array{action: string, payload: array, signed_at: string, signature: string}
     */
    public function buildMeshEnvelope(array $payload, string $action): array
    {
        $envelope = [
            'action'    => $action,
            'payload'   => $payload,
            'signed_at' => now()->toISOString(),
        ];

        $companyId = auth()->user()?->company_id
            ?? $payload['company_id']
            ?? throw new \RuntimeException('MeshSignatureService: cannot resolve company_id for envelope signing.');

        $envelope['signature'] = $this->signPayload($envelope['payload'], (int) $companyId);

        return $envelope;
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * JSON-encode the payload in a deterministic (sorted-key) order.
     */
    private function canonicalise(array $payload): string
    {
        return json_encode(
            $this->canonicaliseValue($payload),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
    }

    /**
     * Recursively canonicalise nested arrays so associative structures have a
     * deterministic key order while preserving sequential list ordering.
     *
     * @return mixed
     */
    private function canonicaliseValue(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $nestedValue) {
            $value[$key] = $this->canonicaliseValue($nestedValue);
        }

        if (! array_is_list($value)) {
            ksort($value);
        }

        return $value;
    }

    /**
     * Resolve the signing secret for this company's own node.
     * Uses the application key as a fallback for single-node deployments.
     */
    private function resolveOwnSecret(int $companyId): string
    {
        $configured = config("mesh.company_secrets.{$companyId}");

        return $configured ?? config('app.key');
    }
}
