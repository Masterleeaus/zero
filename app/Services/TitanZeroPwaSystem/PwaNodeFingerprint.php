<?php

namespace App\Services\TitanZeroPwaSystem;

/**
 * Generates and validates a deterministic node fingerprint from the device registration data.
 *
 * The fingerprint is derived from stable device attributes: node_id + platform + user_agent hash.
 * It serves as a secondary identity signal beyond the node_id UUID.
 */
class PwaNodeFingerprint
{
    private const ALGO = 'sha256';
    private const FINGERPRINT_VERSION = 'v1';

    /**
     * Generate a deterministic fingerprint from registration meta.
     *
     * @param  string  $nodeId  Client-generated UUID
     * @param  string  $platform  e.g. "Win32", "Linux x86_64", "iPhone"
     * @param  string  $userAgent  Navigator user-agent string
     * @param  string  $appVersion  Client app version
     */
    public function generate(string $nodeId, string $platform, string $userAgent, string $appVersion): string
    {
        $parts = [
            self::FINGERPRINT_VERSION,
            $nodeId,
            strtolower($platform),
            hash(self::ALGO, $userAgent),
            $appVersion,
        ];

        return hash(self::ALGO, implode('|', $parts));
    }

    /**
     * Verify a fingerprint submitted by the client against computed expectations.
     *
     * If the stored fingerprint is null (first handshake), this is treated as a new registration.
     *
     * @param  string  $submitted  The fingerprint supplied by the client
     * @param  string|null  $stored  The previously stored fingerprint (null = not yet registered)
     * @param  string  $nodeId  For logging context
     */
    public function verify(string $submitted, ?string $stored, string $nodeId): array
    {
        if ($stored === null) {
            return [
                'ok'     => true,
                'reason' => 'new_node',
                'note'   => 'No stored fingerprint — first registration accepted',
            ];
        }

        if (hash_equals($stored, $submitted)) {
            return ['ok' => true, 'reason' => 'fingerprint_match'];
        }

        return [
            'ok'      => false,
            'reason'  => 'fingerprint_mismatch',
            'node_id' => $nodeId,
        ];
    }

    /**
     * Compute a short display label from the fingerprint (first 12 hex chars).
     */
    public function shortLabel(string $fingerprint): string
    {
        return substr($fingerprint, 0, 12);
    }
}
