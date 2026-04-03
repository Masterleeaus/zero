<?php

namespace App\Services\TitanZeroPwaSystem;

use Illuminate\Support\Facades\Log;

/**
 * Validates signal signatures sent by PWA nodes.
 *
 * Production strategy: HMAC-SHA256 using a shared per-device or global key.
 * The client signs: sha256(node_id + ":" + signal_key + ":" + timestamp + ":" + payload_hash)
 * with the device's pre-shared key, then encodes as hex.
 */
class SignalSignatureValidator
{
    private const SIGNATURE_ALGO = 'sha256';
    private const REPLAY_WINDOW_SECONDS = 300; // 5 minutes

    /**
     * Validate a signal's HMAC signature.
     *
     * @param  array  $signal  The incoming signal payload
     * @param  string  $nodeId  The originating node ID
     * @param  string|null  $deviceKey  Per-device pre-shared key (null → global fallback key)
     */
    public function validate(array $signal, string $nodeId, ?string $deviceKey = null): array
    {
        $signature = $signal['signature'] ?? null;

        if (empty($signature)) {
            return [
                'valid'  => false,
                'reason' => 'No signature provided',
                'code'   => 'missing_signature',
            ];
        }

        // Timestamp replay protection
        $tsCheck = $this->checkTimestampReplay($signal);
        if (! $tsCheck['valid']) {
            return $tsCheck;
        }

        $key = $deviceKey ?? $this->globalKey();

        if (empty($key)) {
            // No key configured — treat as structurally valid but unverified
            return [
                'valid'  => true,
                'reason' => 'No signing key configured; structural pass only',
                'code'   => 'unverified',
            ];
        }

        $expected = $this->computeExpected($signal, $nodeId, $key);

        if (! hash_equals($expected, strtolower((string) $signature))) {
            Log::warning('[SignalSignatureValidator] Signature mismatch', [
                'node_id'    => $nodeId,
                'signal_key' => $signal['signal_key'] ?? null,
            ]);

            return [
                'valid'  => false,
                'reason' => 'Signature mismatch',
                'code'   => 'invalid_signature',
            ];
        }

        return [
            'valid'  => true,
            'reason' => 'Signature verified',
            'code'   => 'ok',
        ];
    }

    /**
     * Compute the expected HMAC for a signal.
     */
    public function computeExpected(array $signal, string $nodeId, string $key): string
    {
        $signalKey  = $signal['signal_key'] ?? '';
        $timestamp  = $signal['timestamp'] ?? '';
        $payloadStr = is_array($signal['payload'] ?? null)
            ? json_encode($signal['payload'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : (string) ($signal['payload'] ?? '');

        $data = implode(':', [$nodeId, $signalKey, (string) $timestamp, $payloadStr]);

        return hash_hmac(self::SIGNATURE_ALGO, $data, $key);
    }

    /**
     * Check timestamp for replay attacks.
     * Supports both Unix timestamp (seconds) and millisecond timestamps from JS Date.now().
     */
    private function checkTimestampReplay(array $signal): array
    {
        $ts = $signal['timestamp'] ?? null;

        if (empty($ts)) {
            return ['valid' => false, 'reason' => 'Missing timestamp for replay check', 'code' => 'missing_timestamp'];
        }

        // Detect JS millisecond timestamps (> 1e10 implies ms since epoch)
        if (is_numeric($ts) && $ts > 1e10) {
            $unix = (int) ($ts / 1000);
        } elseif (is_numeric($ts)) {
            $unix = (int) $ts;
        } else {
            $unix = strtotime((string) $ts);
        }

        if (! $unix) {
            return ['valid' => false, 'reason' => 'Unparseable timestamp', 'code' => 'bad_timestamp'];
        }

        $age = abs(time() - $unix);

        if ($age > self::REPLAY_WINDOW_SECONDS) {
            return [
                'valid'  => false,
                'reason' => "Timestamp {$age}s outside replay window of ".self::REPLAY_WINDOW_SECONDS.'s',
                'code'   => 'replay_window',
            ];
        }

        return ['valid' => true, 'reason' => 'Timestamp within replay window', 'code' => 'ok'];
    }

    private function globalKey(): string
    {
        return (string) config('pwa.signing_key', '');
    }
}
