<?php

namespace App\Services\TitanCoreConsensus;

use Illuminate\Support\Facades\Log;

class EquilibriumResolver
{
    private const SERVER_AUTHORITY_THRESHOLD_SECONDS = 60;

    /**
     * Resolve a conflict between server and client records.
     *
     * @param  array{server_record: array, client_record: array, device_trust_level: string}  $conflict
     */
    public function resolve(array $conflict): array
    {
        $server = $this->normalize($conflict['server_record'] ?? []);
        $client = $this->normalize($conflict['client_record'] ?? []);
        $deviceTrustLevel = $conflict['device_trust_level'] ?? 'untrusted';
        $log = [];

        // Server authority override: server timestamp newer by more than threshold
        $serverTs = strtotime($server['updated_at'] ?? $server['timestamp'] ?? '') ?: 0;
        $clientTs = strtotime($client['updated_at'] ?? $client['timestamp'] ?? '') ?: 0;

        if ($serverTs > 0 && ($serverTs - $clientTs) > self::SERVER_AUTHORITY_THRESHOLD_SECONDS) {
            $log[] = [
                'check' => 'server_authority',
                'result' => 'server wins — newer by '.($serverTs - $clientTs).'s',
            ];

            $resolution = [
                'winner'   => 'server',
                'resolved' => $server,
                'log'      => $log,
                'reason'   => 'Server record is authoritative (timestamp newer by more than '.self::SERVER_AUTHORITY_THRESHOLD_SECONDS.'s)',
            ];

            $this->log($resolution);

            return $resolution;
        }

        $log[] = ['check' => 'server_authority', 'result' => 'no override — server not newer by threshold'];

        // Permission downgrade rejection: client cannot lower trust level
        $trustLevels = TriCoreConsensus::TRUST_LEVELS;
        $currentTrust = $trustLevels[$server['trust_level'] ?? 'untrusted'] ?? 0;
        $clientTrust = $trustLevels[$client['trust_level'] ?? 'untrusted'] ?? 0;

        if ($clientTrust < $currentTrust) {
            $log[] = [
                'check'  => 'permission_downgrade',
                'result' => 'rejected — client attempted to downgrade trust from '
                    .($server['trust_level'] ?? 'untrusted').' to '.($client['trust_level'] ?? 'untrusted'),
            ];

            $resolution = [
                'winner'   => 'server',
                'resolved' => $server,
                'log'      => $log,
                'reason'   => 'Permission downgrade rejected — client cannot lower trust level',
            ];

            $this->log($resolution);

            return $resolution;
        }

        $log[] = ['check' => 'permission_downgrade', 'result' => 'passed'];

        // Default: client wins when no overriding condition applies
        $log[] = ['check' => 'default_resolution', 'result' => 'client record accepted'];

        $resolution = [
            'winner'   => 'client',
            'resolved' => $client,
            'log'      => $log,
            'reason'   => 'Client record accepted — no server authority override or permission downgrade',
        ];

        $this->log($resolution);

        return $resolution;
    }

    /**
     * Normalize timestamps to UTC and clean null values.
     */
    public function normalize(array $record): array
    {
        foreach (['created_at', 'updated_at', 'timestamp', 'wrapped_at'] as $field) {
            if (! empty($record[$field])) {
                $ts = strtotime($record[$field]);
                if ($ts !== false) {
                    $record[$field] = gmdate('Y-m-d\TH:i:s\Z', $ts);
                }
            }
        }

        // Remove null values to keep records clean
        return array_filter($record, fn ($v) => $v !== null);
    }

    /**
     * Write resolution to the application log.
     */
    public function log(array $resolution): void
    {
        Log::channel('stack')->info('[EquilibriumResolver] Conflict resolved', $this->auditSafe($resolution));
    }

    /**
     * Strip sensitive fields for audit output.
     */
    public function auditSafe(array $resolution): array
    {
        $sensitiveKeys = ['signature', 'token', 'secret', 'password', 'api_key'];

        $strip = function (array $arr) use ($sensitiveKeys, &$strip): array {
            foreach ($arr as $key => $value) {
                if (in_array(strtolower((string) $key), $sensitiveKeys, true)) {
                    $arr[$key] = '[REDACTED]';
                } elseif (is_array($value)) {
                    $arr[$key] = $strip($value);
                }
            }

            return $arr;
        };

        return $strip($resolution);
    }
}
