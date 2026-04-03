<?php

namespace App\TitanCore\Zylos;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\DB;

/**
 * ZylosBridge — canonical gateway to the Zylos skill execution runtime.
 *
 * Canonical location: App\TitanCore\Zylos\ZylosBridge
 *
 * Responsibilities:
 *   - Admin monitoring: status snapshot of all registered skills (DB-backed)
 *   - Skill control:    restart / disable skill processes (DB event log)
 *   - Async dispatch:   send skill execution requests to the Zylos HTTP runtime
 *   - Runtime query:    status of a specific execution by ID
 *   - Skill listing:    enumerate skills known to the Zylos runtime
 *   - Callback auth:    validate HMAC-signed inbound Zylos callbacks
 *
 * Rules:
 *   - Zylos is a sidecar / async skill runtime only.
 *   - No direct DB ownership beyond the tz_skill_events / tz_skill_registry event log.
 *   - Signal / Memory / Rewind remain source of truth.
 *   - Signed callbacks or canonical Laravel processing remain authoritative.
 */
class ZylosBridge
{
    public function __construct(
        protected HttpFactory $http,
    ) {
    }

    // ─── Admin monitoring ────────────────────────────────────────────────────

    /**
     * Return a snapshot of all registered skill statuses (admin monitor).
     *
     * @return array<string, mixed>
     */
    public function status(): array
    {
        $registered = $this->registeredSkills();
        $running    = [];
        $failed     = [];

        $enriched = [];
        foreach ($registered as $skill) {
            $name = $skill['name'] ?? 'unknown';
            try {
                $last = DB::table('tz_skill_events')
                    ->where('skill_name', $name)
                    ->orderByDesc('created_at')
                    ->first();

                $state = $last ? $last->event_type : 'unknown';

                if ($state === 'skill.failed') {
                    $failed[] = $name;
                } elseif ($state === 'skill.started') {
                    $running[] = $name;
                }

                $skill['last_event']     = $state;
                $skill['last_heartbeat'] = $last->created_at ?? null;
                $skill['last_payload']   = $last ? json_decode($last->payload ?? '{}', true) : null;
            } catch (\Throwable) {
                $skill['last_event']     = 'unknown';
                $skill['last_heartbeat'] = null;
                $skill['last_payload']   = null;
            }

            $enriched[] = $skill;
        }

        return [
            'status'     => 'reachable',
            'registered' => $enriched,
            'running'    => $running,
            'failed'     => $failed,
        ];
    }

    /**
     * Restart a named skill process (admin control).
     *
     * @return array<string, mixed>
     */
    public function restart(string $skill): array
    {
        $this->recordEvent($skill, 'skill.restarted', []);

        return ['ok' => true, 'skill' => $skill, 'action' => 'restart_queued'];
    }

    /**
     * Disable a named skill process (admin control).
     *
     * @return array<string, mixed>
     */
    public function disable(string $skill): array
    {
        $this->recordEvent($skill, 'skill.disabled', []);

        return ['ok' => true, 'skill' => $skill, 'action' => 'disabled'];
    }

    // ─── Async dispatch ──────────────────────────────────────────────────────

    /**
     * Dispatch a skill execution request to the Zylos HTTP runtime.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function dispatch(string $skillSlug, array $payload): array
    {
        $endpoint = rtrim((string) config('titan_core.zylos.endpoint', ''), '/');
        $secret   = (string) config('titan_core.zylos.secret', '');

        if ($endpoint === '') {
            return [
                'ok'      => false,
                'status'  => 'misconfigured',
                'message' => 'ZYLOS_ENDPOINT is not configured.',
                'skill'   => $skillSlug,
            ];
        }

        $body = array_merge($payload, [
            'skill'         => $skillSlug,
            'dispatched_at' => now()->toIso8601String(),
        ]);

        $signature = hash_hmac('sha256', json_encode($body, JSON_UNESCAPED_UNICODE), $secret);

        try {
            $response = $this->http
                ->withHeaders([
                    'X-Zylos-Signature' => $signature,
                    'Content-Type'      => 'application/json',
                    'Accept'            => 'application/json',
                ])
                ->timeout((int) config('titan_core.zylos.timeout', 10))
                ->post("{$endpoint}/dispatch", $body);

            return [
                'ok'            => $response->successful(),
                'status'        => $response->status(),
                'skill'         => $skillSlug,
                'response'      => $response->json() ?? [],
                'dispatched_at' => now()->toIso8601String(),
            ];
        } catch (\Throwable $e) {
            return [
                'ok'      => false,
                'status'  => 'error',
                'skill'   => $skillSlug,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Query the runtime status of a specific dispatched skill execution.
     *
     * @return array<string, mixed>
     */
    public function executionStatus(string $executionId): array
    {
        $endpoint = rtrim((string) config('titan_core.zylos.endpoint', ''), '/');
        $secret   = (string) config('titan_core.zylos.secret', '');

        if ($endpoint === '') {
            return ['ok' => false, 'status' => 'misconfigured'];
        }

        $body      = ['execution_id' => $executionId];
        $signature = hash_hmac('sha256', json_encode($body, JSON_UNESCAPED_UNICODE), $secret);

        try {
            $response = $this->http
                ->withHeaders(['X-Zylos-Signature' => $signature])
                ->timeout((int) config('titan_core.zylos.timeout', 10))
                ->post("{$endpoint}/status", $body);

            return [
                'ok'           => $response->successful(),
                'status'       => $response->status(),
                'execution_id' => $executionId,
                'response'     => $response->json() ?? [],
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Return list of available skills from the Zylos HTTP runtime.
     *
     * @return array<string, mixed>
     */
    public function list(): array
    {
        $endpoint = rtrim((string) config('titan_core.zylos.endpoint', ''), '/');

        if ($endpoint === '') {
            return ['ok' => false, 'status' => 'misconfigured', 'skills' => []];
        }

        try {
            $response = $this->http
                ->timeout((int) config('titan_core.zylos.timeout', 10))
                ->get("{$endpoint}/skills");

            return [
                'ok'     => $response->successful(),
                'skills' => $response->json('skills') ?? [],
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'skills' => [], 'message' => $e->getMessage()];
        }
    }

    /**
     * Validate an inbound Zylos callback HMAC signature.
     */
    public function validateCallback(string $rawBody, string $incomingSignature): bool
    {
        $secret   = (string) config('titan_core.zylos.secret', '');
        $expected = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, $incomingSignature);
    }

    // ─── Internal helpers ────────────────────────────────────────────────────

    /**
     * Return list of registered skills from config or DB.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function registeredSkills(): array
    {
        $configured = config('titan_core.skills.registered', []);

        if (! empty($configured)) {
            return array_values($configured);
        }

        try {
            return DB::table('tz_skill_registry')
                ->get()
                ->map(fn ($r) => (array) $r)
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Record a skill lifecycle event in the event log.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function recordEvent(string $skill, string $event, array $payload): void
    {
        try {
            DB::table('tz_skill_events')->insert([
                'skill_name'  => $skill,
                'event_type'  => $event,
                'payload'     => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'created_at'  => now(),
            ]);
        } catch (\Throwable) {
            // Non-fatal: table may not exist yet
        }
    }
}
