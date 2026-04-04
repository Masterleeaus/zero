<?php

declare(strict_types=1);

namespace App\Services\Mesh;

use App\Models\Mesh\MeshNode;

class MeshSignatureService
{
    private const ALGORITHM = 'sha256';

    /**
     * Sign a payload using this node's signing key.
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
     * The signature covers the full envelope (action + payload + signed_at) to
     * prevent cross-endpoint replay and to bind the timestamp for freshness checks.
     *
     * @return array{action: string, payload: array, signed_at: string, signature: string}
     */
    public function buildMeshEnvelope(array $payload, string $action): array
    {
        $companyId = auth()->user()?->company_id
            ?? $payload['company_id']
            ?? throw new \RuntimeException(
                'MeshSignatureService: cannot resolve company_id for envelope signing. ' .
                'Either authenticate a user or include company_id in the payload.'
            );

        $envelope = [
            'action'    => $action,
            'payload'   => $payload,
            'signed_at' => now()->toISOString(),
        ];

        // Sign the full envelope (excluding the signature key itself) so action and
        // timestamp are bound to the signature, preventing cross-endpoint replay.
        $signable = [
            'action'    => $envelope['action'],
            'payload'   => $envelope['payload'],
            'signed_at' => $envelope['signed_at'],
        ];

        $envelope['signature'] = $this->signPayload($signable, (int) $companyId);

        return $envelope;
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * JSON-encode in a deterministic (recursively sorted-key) order.
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
     * Resolve the signing secret for this node.
     *
     * Uses the application key for single-node deployments. Supports Laravel
     * application keys stored either as raw strings or in the usual
     * "base64:{encoded-bytes}" format.
     *
     * Per-company mesh secrets can be introduced later by wiring them through a
     * dedicated config/env source (e.g., config('mesh.company_secrets.{id}')).
     */
    private function resolveOwnSecret(int $companyId): string
    {
        $appKey = config('app.key');

        if (! is_string($appKey) || $appKey === '') {
            throw new \RuntimeException(
                'MeshSignatureService: app.key must be configured to sign mesh payloads.'
            );
        }

        if (str_starts_with($appKey, 'base64:')) {
            $decoded = base64_decode(substr($appKey, 7), true);

            if ($decoded === false) {
                throw new \RuntimeException(
                    'MeshSignatureService: app.key uses an invalid base64 encoding.'
                );
            }

            return $decoded;
        }

        return $appKey;
    }
}
